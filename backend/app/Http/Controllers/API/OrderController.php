<?php

namespace App\Http\Controllers\API;

use App\Events\Notifications\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    private function userId(): ?int
    {
        return auth('api')->check() ? auth('api')->id() : null;
    }

    private function sessionId(Request $request): ?string
    {
        return $request->header('X-Session-ID')
            ?: $request->header('X-Session-Id')
            ?: $request->input('session_id')
            ?: session()->getId();
    }

    /**
     * List authenticated user's orders
     */
    public function index(Request $request)
    {
        $userId = $this->userId();
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $orders = Order::where('user_id', $userId)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get specific order details (strictly scoped to owner to prevent IDOR)
     */
    public function show($id)
    {
        $userId = $this->userId();
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $order = Order::where('user_id', $userId)
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('order_number', $id);
                } else {
                    $q->where('order_number', $id);
                }
            })
            ->with(['items.product'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Checkout and create order from current cart with pessimistic concurrency locking
     */
    public function store(Request $request)
    {
        $userId = $this->userId();
        if (!$userId) {
            return response()->json(['message' => 'Authentication required for checkout.'], 401);
        }

        $validated = $request->validate([
            'shipping_address' => 'required|string|max:500',
            'billing_address' => 'nullable|string|max:500',
            'payment_method' => 'required|string|in:stripe,paymob,myfatoorah',
            'notes' => 'nullable|string|max:500',
        ]);

        $sessionId = $this->sessionId($request);

        return DB::transaction(function () use ($userId, $sessionId, $validated) {
            // 1. Fetch user's cart items with pessimistic row lock
            $cartItems = Cart::where(function ($q) use ($userId, $sessionId) {
                $q->where('user_id', $userId);
                if ($sessionId) {
                    $q->orWhere('session_id', $sessionId);
                }
            })->lockForUpdate()->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty. Add items before checkout.',
                ], 422);
            }

            // 2. Validate current stock and compute prices authoritatively from database
            $totalAmount = 0.00;
            $orderItemsData = [];

            foreach ($cartItems as $item) {
                // Pessimistically lock product row to prevent TOCTOU race conditions
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A product in your cart is no longer available.',
                    ], 422);
                }

                $quantity = max(1, (int) $item->quantity);

                // Inventory check
                if ($product->quantity < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for '{$product->name}'. Only {$product->quantity} available.",
                    ], 422);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $quantity, 2);
                $totalAmount += $lineTotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'total' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Sanitize input to prevent Stored XSS
            $shippingAddress = strip_tags($validated['shipping_address']);
            $billingAddress = strip_tags($validated['billing_address'] ?? $validated['shipping_address']);
            $notes = isset($validated['notes']) ? strip_tags($validated['notes']) : null;

            // 3. Create Order
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'notes' => $notes,
            ]);

            // 4. Insert Order Items
            foreach ($orderItemsData as &$data) {
                $data['order_id'] = $order->id;
            }
            OrderItem::insert($orderItemsData);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'currency' => 'USD',
                    'payment_method' => $order->payment_method,
                    'status' => $order->status,
                ],
            ], 201);
        });
    }
}
