# Multi-Gateway Payment System — Architecture, Security Audit & Testing Guide

## 1. End-to-End Payment Cycle Architecture

The payment pipeline connects the React PWA storefront (`frontend/src/components/Cart/PaymentPage.jsx`) to the Laravel 10 API (`backend/app/Http/Controllers/API/PaymentController.php`) using a Strategy + Factory + Chain-of-Responsibility + Idempotency architecture.

```
[React Checkout UI]
   │  1. POST /api/orders (creates authoritative order from DB cart + sale prices + coupons)
   ▼
[OrderController::store] ──► Sorted product row locks (lockForUpdate) → Order (unpaid/pending)
   │
   │  2. POST /api/payment/charge (order_id, gateway, stable idempotency_key, PCI-safe metadata)
   ▼
[PaymentController::charge]
   ├── Ownership check (order.user_id === auth('api')->id())
   ├── Strip any raw PAN/CVC fields (PCI-DSS defense-in-depth)
   ▼
[PaymentProcessor::process]
   ├── Step 1: IdempotencyHandler (checkDuplicate + SHA-256 payload integrity check)
   ├── Step 2: ValidationHandler → LoggingHandler (Chain of Responsibility)
   ├── Step 3: PaymentGatewayFactory → StripeGateway | PaymobGateway | MyFatoorahGateway
   │           └── Smart Retry with Exponential Backoff (retries transient 5xx/429 only; NEVER retries card declines)
   └── Step 4: Record outcome in `idempotent_transactions`
   │
   ├── If status === 'completed': Atomic settlement (ordered lockForUpdate stock decrement + mark paid + clear cart + OrderCreated event)
   └── If status === 'pending' (Live Redirect / 3DS): Return `payment_url` / `client_secret` → Settle asynchronously via verified Webhook
```

---

## 2. Security & Performance Audit — Issues Found & Fixed

| # | Severity | Issue Discovered | Fix Implemented |
|---|---|---|---|
| 1 | **Critical (Security)** | `POST /api/payment/refund` was accessible to **any** logged-in customer (`auth:api`), allowing unauthorized refunds. | Enforced Admin/Super-Admin authorization in `PaymentController::refund()`, added refund-ceiling validation (`amount <= order.total_amount`), and prevented double refunds. |
| 2 | **Critical (Security)** | `POST /api/payment/webhook` pointed to non-existent `handleWebhook` method AND had **zero cryptographic signature verification**. | Fixed route to `PaymentController::webhook` and added `verifyWebhookSignature()` to all 3 gateways (Stripe `Stripe-Signature` HMAC-SHA256 + 5-min replay window, Paymob canonical HMAC-SHA512, MyFatoorah `MyFatoorah-Signature` HMAC-SHA256). Invalid signatures return `401`. |
| 3 | **High (Integrity)** | `IdempotencyHandler::hashRequest()` read snake_case (`order_id`) from a camelCase DTO cast (`orderId`), making tamper detection silently hash empty strings. Also had lowercase `idempotentTransaction::create()` (breaks Linux/Docker). | Normalized both snake_case and camelCase keys with fixed-precision `amount` formatting, and fixed class casing to `IdempotentTransaction::create()`. |
| 4 | **High (Logic)** | `PaymentController::charge()` settled orders as `paid` even when a gateway returned `status = 'pending'` (redirect / 3DS checkout). | Added `PaymentResultDTO::pending()` and restricted immediate settlement strictly to `$result->success && $result->status === 'completed'`. |
| 5 | **High (Pricing)** | `OrderController::store()` charged regular `product.price` instead of active `product.special_price`, mismatching cart/checkout totals. | Updated `OrderController::store()` to authoritatively compute effective sale price (`special_price` when active and `< price`). |
| 6 | **Medium (Bug)** | `MyFatoorahGateway` was commented out in `PaymentGatewayFactory`, throwing an exception if selected at checkout. | Registered `myfatoorah` in `PaymentGatewayFactory`. |
| 7 | **Medium (Performance)** | Locking `products` rows in arbitrary cart order risked MySQL deadlocks under concurrent checkouts. | Sorted cart/order items by `product_id` ascending prior to `lockForUpdate()` in both `OrderController::store()` and `PaymentController::settleOrderAtomically()`. |
| 8 | **Medium (Performance)** | Retry loop retried deterministic card declines (`insufficient_funds`, `expired_card`) 3 times, tripling latency and hitting gateway rate limits. | Added `retryable` flag so only transient network / 5xx / 429 errors are retried with exponential backoff + jitter. |
| 9 | **Medium (Frontend UX/PCI)** | `PaymentPage.jsx` generated a new `idempotency_key` on every click (defeating double-click protection), lacked card inputs/test presets, and showed fake hardcoded items on empty cart. | Added stable per-attempt idempotency key, PCI-safe card form (never sends raw PAN/CVC to backend), redirect support for `payment_url`, and one-click sandbox test card presets. |

---

## 3. Environment Configuration (`backend/.env`)

When API keys are **empty**, all 3 gateways automatically operate in **Interactive Sandbox Mode** (so you can test full checkout + decline scenarios immediately). As soon as you add your keys below, the gateways automatically switch to **Live/Test Provider APIs**:

```env
# Stripe (https://dashboard.stripe.com/test/apikeys)
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Paymob Unified Intention API (https://accept.paymob.com)
PAYMOB_BASE_URL=https://accept.paymob.com
PAYMOB_SECRET_KEY=egy_sk_test_...
PAYMOB_PUBLIC_KEY=egy_pk_test_...
PAYMOB_INTEGRATION_ID=1234567
PAYMOB_HMAC_SECRET=your_hmac_secret

# MyFatoorah v2 (Sandbox: https://apitest.myfatoorah.com | Live: https://api.myfatoorah.com)
MYFATOORAH_BASE_URL=https://apitest.myfatoorah.com
MYFATOORAH_API_KEY=your_myfatoorah_bearer_token
MYFATOORAH_WEBHOOK_SECRET=your_webhook_secret

FRONTEND_URL=http://localhost:3000
```

---

## 4. Test Card Matrix (How to Test a Product Right Now)

1. Add any product to your cart on `http://localhost:3000`.
2. Go to **Cart → Proceed to Checkout** (ensure you are signed in).
3. Choose **Stripe**, **Paymob**, or **MyFatoorah**, then enter or click one of the **Quick-Fill Sandbox Test Cards**:

| Gateway | Test Scenario | Card Number | Expiry | CVC | Expected Result |
|---|---|---|---|---|---|
| **Stripe / Any** | Visa Success | `4242 4242 4242 4242` | `12/28` | `424` | Order confirmed, stock decremented, cart cleared, visible in `/orders` |
| **Paymob** | Paymob Visa Success | `4987 6543 2109 8769` | `12/28` | `123` | Order confirmed (or redirects to Paymob Unified Checkout if live keys set) |
| **MyFatoorah** | Visa / KNET Success | `4242 4242 4242 4242` | `12/28` | `123` | Order confirmed (or redirects to MyFatoorah InvoiceURL if live keys set) |
| **Any** | Declined (Insufficient Funds) | `4000 0000 0000 9995` | `12/28` | `123` | `400` error shown immediately (0 retries), order stays `unpaid`, can retry with valid card |
| **Any** | Expired Card | `4242 4242 4242 0002` | `01/22` | `123` | `400` expired card error shown immediately |

---

## 5. Local Webhook Testing

- **Stripe CLI:**
  ```bash
  stripe listen --forward-to http://127.0.0.1:8000/api/payment/webhook?gateway=stripe
  ```
- **Paymob / MyFatoorah (via ngrok):**
  ```bash
  ngrok http 8000
  # Set callback/webhook URL in dashboard to:
  # https://<your-ngrok>.ngrok-free.app/api/payment/webhook?gateway=paymob
  # https://<your-ngrok>.ngrok-free.app/api/payment/webhook?gateway=myfatoorah
  ```
