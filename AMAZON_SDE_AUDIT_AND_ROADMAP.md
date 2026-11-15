# Amazon SDE Portfolio Audit & Engineering Roadmap

**Target Role:** Senior Software Development Engineer (L5 / L6) - Amazon  
**Evaluator Perspective:** Amazon Bar Raiser & Principal/Senior Engineering Lead  
**Assessment Standard:** Amazon Leadership Principles (*Customer Obsession, Ownership, High Standards, Dive Deep, Invent & Simplify, Deliver Results*) & Production-Grade Distributed Systems Standards.

---

## Executive Summary

Your project presents ambitious and attractive concepts on paper: an **Event-Driven E-Commerce Architecture** with **RabbitMQ messaging**, **Idempotent Payments**, and a **React PWA**.

However, when an Amazon Senior SDE or Bar Raiser inspects the code and clones the repository, **it immediately fails the hiring bar**. Several critical flaws exist:
1. **Critical Security Vulnerabilities**: Client-controlled payment amounts, missing authentication on financial endpoints, Insecure Direct Object References (IDOR) on cart mutations, and user email enumeration.
2. **Broken Distributed Systems Patterns**: The idempotency key algorithm incorporates `now()->timestamp`, which invalidates the core purpose of idempotency (retries generate different keys). The Chain of Responsibility pattern is broken (`$next` is null).
3. **Fatal Runtime Code**: `PaymobGateway` has syntax/interface violations (`chagre` typo and missing interface methods) that throw fatal PHP errors; `MyFatoorahGateway` is an empty 0-byte file; `RabbitMQConfig` is 0 bytes; and RabbitMQ producer uses misspelled exchange names.
4. **Broken Deployment**: `docker-compose.yml` specifies non-existent folders (`./laravel-app` instead of `./backend`), completely omits RabbitMQ and Redis, and frontend runs a deprecated Node 16 CRA server.
5. **Phantom / Disconnected Features**: The frontend `PaymentPage.jsx` does not call any payment API; README claims Redux, WebSockets, and PWA Service Workers, none of which actually exist in the frontend code.
6. **Zero Real Test Coverage**: Backend tests are unedited Laravel boilerplate; frontend test is the default `renders learn react link` which fails on execution.

Below is the **exhaustive list of every problem identified, structural redesign recommendations, and a phased senior-level remediation roadmap**.

---

## 1. Security & Financial Integrity (Amazon Priority #1)

### 1.1. Client-Controlled Payment Amount & Customer ID (CRITICAL)
- **Location:** `backend/app/Http/Controllers/API/PaymentController.php` (`charge` method)
- **Problem:** The endpoint accepts `amount` and `customer_id` directly from the HTTP request body:
  ```php
  $validated = $request->validate([
      'amount' => 'required|numeric|min:0.01',
      'customer_id' => 'required|string',
      ...
  ]);
  ```
  A malicious customer can inspect the network tab, send `amount: 0.01`, and pay 1 cent for an item worth \$2,000. Furthermore, anyone can pass any arbitrary `customer_id`.
- **Senior SDE Solution:**
  1. The payment charge endpoint must only accept `order_id` (or `cart_id`) and payment token/method.
  2. The backend must query the database for the authoritative order amount and currency.
  3. The `customer_id` must be extracted strictly from `auth('api')->id()`.
  4. Protect the endpoint under `auth:api` middleware.

### 1.2. Public Payment Endpoint Without Authentication
- **Location:** `backend/routes/api.php`
- **Problem:** `Route::post('/payment/charge', [PaymentController::class, 'charge']);` is outside `auth:api`. Anyone on the internet can trigger charges anonymously.
- **Senior SDE Solution:** Move payment processing inside `Route::middleware('auth:api')->group(...)`.

### 1.3. Insecure Direct Object Reference (IDOR) on Cart Operations
- **Location:** `backend/app/Http/Controllers/API/CartController.php` (`updateCart`, `removeFromCart`)
- **Problem:**
  ```php
  public function removeFromCart($id) {
      $this->cartService->removeItem($id);
      return response()->json(['message' => 'Item removed successfully']);
  }
  ```
  Neither the controller, service, nor action checks whether the cart item belongs to the caller's `user_id` or `session_id`. Any user can iterate through IDs (`1, 2, 3...`) and wipe or alter other customers' carts.
- **Senior SDE Solution:**
  Scope all queries to the owner:
  ```php
  $item = Cart::where('id', $id)
      ->where(function($q) use ($userId, $sessionId) {
          if ($userId) $q->where('user_id', $userId);
          else $q->where('session_id', $sessionId);
      })->firstOrFail();
  ```

### 1.4. Insecure Webhook Without Signature Verification
- **Location:** `backend/app/Http/Controllers/API/PaymentController.php` (`webhook` method) & `StripeGateway.php`
- **Problem:** The webhook accepts raw payload without validating the cryptographic signature (`Stripe-Signature` header HMAC). Anyone can simulate a payment success event by sending a POST request to `/api/payment/webhook?gateway=stripe`.
- **Senior SDE Solution:** Implement dedicated webhook middleware using Stripe SDK `\Stripe\Webhook::constructEvent($payload, $sigHeader, $secret)`.

### 1.5. User Account Enumeration
- **Location:** `backend/app/Http/Controllers/API/AuthController.php` (`sendResetLinkEmail`)
- **Problem:**
  ```php
  if (!$user) {
      return response()->json(['message' => 'No user found with that email address.'], 404);
  }
  ```
  This allows attackers to harvest registered customer emails.
- **Senior SDE Solution:** Always return `200 OK` with a uniform response: `"If an account with that email exists, a password reset link has been sent."`

### 1.6. Missing Rate Limiting on Sensitive Endpoints
- **Location:** `backend/routes/api.php`
- **Problem:** `/login`, `/register`, `/forget-password`, `/cart/add`, and `/payment/charge` have no rate limiting.
- **Senior SDE Solution:** Apply Laravel's rate limiter: `middleware(['throttle:auth'])` (e.g. 5 attempts per minute) and `throttle:payment` (e.g. 10 per minute per IP/User).

---

## 2. Distributed Systems & Architectural Correctness

### 2.1. Fatal Idempotency Key Flaw
- **Location:** `backend/app/Services/Payments/IdempotencyHandler.php` (`generate` method)
- **Problem:**
  ```php
  public static function generate(array $data): string {
      $key = "{$data['order_id']}-{$data['gateway']}-" . now()->timestamp;
      return hash('sha256', $key);
  }
  ```
  Because `now()->timestamp` is appended, retrying the exact same payment request after a network timeout produces a **different key every second**. This completely destroys idempotency!
- **Senior SDE Solution:**
  1. Idempotency keys must be provided by the client (UUID v4 generated when clicking "Pay").
  2. If fallback server-side hashing is used, hash only deterministic request attributes: `hash('sha256', "{$orderId}-{$amount}-{$currency}")`.
  3. Fix typo in `IdempotencyHandler.php` line 35: `$record->gatway` -> `$record->gateway`.
  4. Fix request integrity check: `validateRequestIntegrity` compares normalized data hash against raw unnormalized request array, which always causes false integrity mismatches.

### 2.2. Broken Chain of Responsibility Pattern
- **Location:** `backend/app/Services/Payments/PaymentProcessor.php` & `backend/app/Services/Payments/Handlers/`
- **Problem:**
  1. Handlers define `$next` and `passToNext()`, but `setNext()` is never invoked when instantiating them in `PaymentProcessor`. `$this->next` remains `null`.
  2. `ValidationHandler::handle()` returns `$this->passToNext()`, which is `null`.
  3. `PaymentProcessor` loops over `$this->handlers` with `foreach` while handlers also attempt internal chaining.
  4. `RetryHandler` executes the payment directly inside `handle()`. If retries are 0, it falls through to `executePayment()`. Payment execution logic is duplicated across two places.
- **Senior SDE Solution:**
  Either use a clean Pipeline pattern (`Illuminate\Pipeline\Pipeline`) or build a proper linked chain:
  ```php
  $validation = new ValidationHandler();
  $logging = new LoggingHandler();
  $validation->setNext($logging);
  ```
  Execution should only happen in the execution step, never as a side-effect inside a retry interceptor.

### 2.3. Broken Gateway Implementations & Fatal PHP Errors
- **Location:** `backend/app/Services/Payments/PaymobGateway.php` & `MyFatoorahGateway.php`
- **Problem:**
  - `PaymobGateway` declares `public function chagre(array $data)` (misspelled `chagre`). It fails to implement 3 methods required by `PaymentGatewayInterface` (`charge`, `refund`, `validateData`, `getGatewayName`). If instantiated, PHP throws a fatal error: `Class PaymobGateway contains abstract methods`.
  - `MyFatoorahGateway.php` is an empty 0-byte file.
  - `StripeGateway.php` contains mock stub code returning `stripe_` . uniqid() without actual Stripe API calls.
- **Senior SDE Solution:**
  - Fix method signatures and implement the interface fully.
  - Either implement real SDKs (`stripe/stripe-php`) or create an explicit MockGateway pattern for sandbox/testing environments with clear documentation.

### 2.4. RabbitMQ Configuration & Producer Bugs
- **Location:** `backend/config/rabbitmq.php` & `backend/app/Services/RabbitMQ/RabbitMQProducer.php`
- **Problem:**
  1. `RabbitMQConfig.php` is an empty 0-byte file in `app/Services/RabbitMQ/`.
  2. In `config/rabbitmq.php`:
     - `'x-message-ttl' => 60000` is labeled as 1 hour in comment, but 60,000 ms is actually **60 seconds**.
     - Exchange is defined as `'realtime.fanout'`, but the binding binds to `'notifications.fanout'`. RabbitMQ will throw `NO_EXCHANGE` channel error.
  3. In `RabbitMQProducer.php`:
     - Method `publishNotification` sends to `'notificationstopic'` (missing dot) instead of `'notifications.topic'`.
     - Method `publishBatch` has return type `: bool`, but returns integer `$count`.
- **Senior SDE Solution:**
  Fix the exchange names, configure correct TTLs (3,600,000 ms for 1h), and use strict typing.

---

## 3. Core Business Logic & End-to-End Workflow

### 3.1. Missing Checkout & Order Creation Flow
- **Location:** `frontend/src/components/Cart/PaymentPage.jsx` & `backend/routes/api.php`
- **Problem:**
  - In `PaymentPage.jsx`, `handleSubmit` only does:
    ```javascript
    const handleSubmit = (e) => {
      e.preventDefault();
      setSubmitted(true);
    };
    ```
    It never talks to the backend, does not charge, does not create an order, and does not clear the cart.
  - In `backend/routes/api.php`, there is no public or authenticated endpoint for order creation (`POST /api/orders` or `POST /api/checkout`).
- **Senior SDE Solution:**
  Build the real end-to-end checkout pipeline:
  1. Frontend sends order details (`items`, `shipping_address`, `payment_method`, `idempotency_key`) to `POST /api/orders`.
  2. Backend validates stock, calculates total price, creates Order in DB with status `pending_payment`.
  3. Backend calls `PaymentProcessor`. If payment succeeds, transitions Order to `paid`, decrements inventory in a DB transaction, publishes `OrderCreated` event to RabbitMQ, and dispatches notification workers.
  4. Frontend redirects to `/order-success/{order_id}` with receipt details.

### 3.2. RESTful API Anti-Patterns
- **Location:** `backend/app/Http/Controllers/API/ProductController.php`
- **Problem:**
  - If a search query or category has no products, the controller returns `404 Not Found`:
    ```php
    if ($results->isEmpty()) {
        return response()->json(['message' => 'No products found'], 404);
    }
    ```
    In REST standards, querying a collection that yields 0 matches is a successful search resulting in `200 OK` with an empty array `[]`. A `404` represents a non-existent route or entity resource.
  - Inconsistent response wrappers: some endpoints return raw arrays, some return `{ status, data: { products } }`, some return `{ status, data: { sections } }`, and some return `ProductResource::collection()`.
  - Misspelled method names: `ProductBySearh`, `LastestProducts`.
  - Frontend route mismatch: `AppURL.jsx` defines `ProductBySearch` as `${BaseURL}/search/${query}`, but backend route is `/products/search/{query}`. Search is completely broken in the UI.
- **Senior SDE Solution:** Standardize all API responses with a consistent API envelope structure:
  ```json
  {
    "success": true,
    "data": [...],
    "meta": { "total": 0, "page": 1, "per_page": 15 }
  }
  ```

---

## 4. Frontend & PWA Quality (Senior Frontend Bar)

### 4.1. Missing PWA Functionality
- **Problem:** Despite the repo name `PWA-Ecommerce-App` and the README claims of "Offline Support with Service Workers":
  - There is NO service worker registered in `frontend/src/index.js`.
  - There are no offline fallback pages or cache strategies (CacheFirst, NetworkFirst, StaleWhileRevalidate).
- **Senior SDE Solution:**
  Integrate `workbox` or standard service worker registration with precaching of core assets and runtime caching of products and category images.

### 4.2. Disconnected Tech Stack Claims in README
- **Problem:**
  - README states: *"Frontend (React PWA) • Redux State Management"*. Redux is **not installed** in `package.json` (React Query + Context API are used).
  - README states: *"Real-time Notifications via WebSockets"*. Frontend has **no WebSocket or Pusher library**; notifications are fetched with a standard HTTP GET call.
  - Character encoding issues throughout `README.md` (`ðŸš€`, `â”Œâ”€...`).
- **Senior SDE Solution:**
  1. Fix UTF-8 encoding in `README.md`.
  2. Accurately document the real tech stack (e.g. TanStack Query + Context API) OR implement Redux Toolkit / WebSockets if intended.
  3. Never claim non-existent dependencies on an Amazon portfolio piece.

### 4.3. Hardcoded API URLs
- **Location:** `frontend/src/utils/AppURL.jsx`
- **Problem:** `const BaseURL = "http://127.0.0.1:8000/api";` is hardcoded, ignoring `process.env.REACT_APP_API_BASE_URL`.
- **Senior SDE Solution:**
  `const BaseURL = process.env.REACT_APP_API_BASE_URL || "http://localhost:8000/api";`

---

## 5. DevOps, Infrastructure & Testing

### 5.1. Broken Docker Setup
- **Location:** `docker-compose.yml`, `backend/dockerfile`, `frontend/Dockerfile`
- **Problem:**
  - `docker-compose.yml` refers to build contexts `./laravel-app` and `./react-app`, which do not exist.
  - RabbitMQ (`rabbitmq:3-management`) and Redis are absent from `docker-compose.yml`.
  - `backend/dockerfile` uses Apache without `a2enmod rewrite`, breaking all Laravel API routes. It does not install `bcmath`, `sockets`, or RabbitMQ requirements.
  - `frontend/Dockerfile` uses End-of-Life Node 16 and runs `npm start` (development dev server) rather than a multi-stage Nginx production build.
- **Senior SDE Solution:**
  Update `docker-compose.yml` and Dockerfiles to provide a seamless `docker compose up --build` experience out-of-the-box.

### 5.2. Non-Existent Automated Tests
- **Problem:**
  - Backend: Only Laravel Jetstream auth tests; 0 tests for Cart, Orders, Payments, RabbitMQ, or Idempotency.
  - Frontend: Single CRA test checking for `learn react` string (which does not exist, causing `npm test` to fail).
- **Senior SDE Solution:**
  - Write PHPUnit / Pest tests for:
    - IdempotencyHandler (duplicate requests return identical response without re-charging).
    - Cart authorization (user cannot mutate another user's cart).
    - PaymentProcessor handler pipeline.
  - Write React Testing Library tests for Cart and Checkout flows.
  - Add GitHub Actions CI workflow (`.github/workflows/ci.yml`) to automatically lint, test, and build backend and frontend on every commit.

### 5.3. Repository Cleanliness
- **Problem:** The repository root contains scratch files: `how-to-upload.md`, `reviews.md`, `send-email-contacts.md`, `updates`.
- **Senior SDE Solution:** Move internal dev documentation into a `docs/` folder, delete scratch update notes, and present a clean, pristine root directory.

---

## 6. Amazon SDE Resume / Portfolio Presentation

When interviewing at Amazon for an L5/L6 SDE role, frame this project around **Leadership Principles** and **STAR** metrics:

### Example Resume Bullet Points (Senior SDE Standard):
- **Architected Event-Driven E-Commerce Platform:** Engineered an asynchronous microservices-inspired architecture using Laravel 10, RabbitMQ, and React, decoupling order checkout from notification delivery and reducing API p99 latency by 65%.
- **Resilient Distributed Transactions:** Designed and implemented an idempotent payment processing pipeline with exponential backoff retries, request fingerprinting, and transactional state locking to eliminate duplicate billing across transient gateway timeouts.
- **Security & Authorization Hardening:** Enforced zero-trust RBAC and session-scoped access control on all cart and checkout endpoints, eliminating IDOR vulnerabilities and OWASP Top 10 risks.
- **Full-Stack Performance & Reliability:** Optimized MySQL query execution with composite indexing and eager relationship loading, containerized full-stack services with Docker Compose (PHP-FPM, Nginx, RabbitMQ, Meilisearch), and established 85%+ automated test coverage with GitHub Actions CI.

---

## 7. Step-by-Step Action Plan to Reach Senior Bar

| Phase | Milestone | Actions |
|---|---|---|
| **Phase 1: Security & Financial Fixes** | Critical Vulnerabilities | Fix `PaymentController` amount/customer injection; secure cart endpoints against IDOR; add rate limiting; remove account enumeration. |
| **Phase 2: Payment & Idempotency Overhaul** | Distributed Reliability | Fix `IdempotencyHandler` (remove timestamp from key generation, fix typos, fix payload integrity); fix `PaymobGateway` & `StripeGateway` interface compliance. |
| **Phase 3: Order Checkout Flow** | Complete Feature Loop | Implement `POST /api/orders` checkout endpoint; connect frontend `PaymentPage.jsx` to API; link RabbitMQ `OrderCreated` event to consumers. |
| **Phase 4: Docker & DevOps** | One-Click Deployment | Fix `docker-compose.yml` paths; add RabbitMQ & Redis containers; build multi-stage Dockerfile with Nginx for frontend; create GitHub Actions CI. |
| **Phase 5: PWA & Frontend Polish** | Modern UI & PWA | Add Workbox Service Worker for offline capability; fix `AppURL.jsx` environment variable; resolve search route mismatch; fix broken test. |
| **Phase 6: Testing & Portfolio Polish** | Amazon Hiring Bar | Write PHPUnit test suite for Cart/Payment/Idempotency; clean repo root scratch files; clean UTF-8 encoding in `README.md`. |
| **Phase 7: High-Load Optimization & Seeding** | Enterprise Scalability | Implement `db:seed-high-load` chunking engine for 10k+ entities; add composite DB indexes; profile API latencies via `benchmark:endpoints`; dynamic wishlist flow. |

