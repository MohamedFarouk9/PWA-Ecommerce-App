<?php
namespace App\Http\Controllers\API;

use App\DTOs\Cart\CartItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Cart\AddCartItemRequest;
use App\Http\Requests\Api\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

    public function __construct(private CartService $cartService){}

    protected function userId(): ?int
    {
        return auth('api')->check() ? auth('api')->id() : null;
    }

    protected function sessionId(Request $request): ?string
    {
        $sessionId = $request->header('X-Session-ID')
            ?: $request->header('X-Session-Id')
            ?: $request->header('X-SessionId')
            ?: $request->input('session_id')
            ?: $request->cookie('laravel_session');

        if ($sessionId) {
            return $sessionId;
        }

        if ($request->hasSession()) {
            return $request->session()->getId();
        }

        return session()->getId();
    }

    public function getCartItems(Request $request)
    {
        $cartItems = $this->cartService->getCartItems($this->userId(), $this->sessionId($request));
        return CartResource::collection($cartItems);
    }
    public function addToCart(AddCartItemRequest $request)
    {
        $validated = $request->validated();

        $cartItemData = new CartItemData(
            id: null,
            userId: $this->userId(),
            sessionId: $this->sessionId($request),
            productId: $validated['product_id'],
            quantity: $validated['quantity'] ?? 1,
            meta: $validated['meta'] ?? null,
            price: Product::findOrFail($validated['product_id'])->price ?? 0
        );

        $cartItem = $this->cartService->addItem($cartItemData);
        return new CartResource($cartItem->load('product'));
    }

   public function updateCart(UpdateCartItemRequest $request , $id)
   {
        $data = $request->validated();
        $item = $this->cartService->updateItem($id , $data);

        return response()->json([
            'message' => 'Cart item updated successfully',
            'cart_item' => $item
        ]);
   }

    public function removeFromCart($id){
        $this->cartService->removeItem($id);
        return response()->json(['message' => 'Item removed successfully']);
    }

}
