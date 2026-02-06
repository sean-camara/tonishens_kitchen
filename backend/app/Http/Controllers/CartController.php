<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Cart::with('dish.category')
            ->where('user_id', auth()->id())
            ->get();

        return $this->success($items);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'dish_id'  => 'required|exists:dishes,id',
            'quantity' => 'integer|min:1|max:99',
        ]);

        $cart = Cart::updateOrCreate(
            ['user_id' => auth()->id(), 'dish_id' => $request->dish_id],
            ['quantity' => \DB::raw("quantity + " . ($request->quantity ?? 1))]
        );

        // If it was a create, the raw expression doesn't work, set default
        $cart->refresh();

        return $this->success($cart->load('dish'), 'Added to cart');
    }

    public function update(Request $request, $dishId): JsonResponse
    {
        $cart = Cart::where('user_id', auth()->id())
            ->where('dish_id', $dishId)
            ->firstOrFail();

        $request->validate(['quantity' => 'required|integer|min:1|max:99']);
        $cart->update(['quantity' => $request->quantity]);

        return $this->success($cart->load('dish'));
    }

    public function destroy($dishId): JsonResponse
    {
        $cart = Cart::where('user_id', auth()->id())
            ->where('dish_id', $dishId)
            ->firstOrFail();

        $cart->delete();

        return $this->success(null, 'Removed from cart');
    }

    public function clear(): JsonResponse
    {
        Cart::where('user_id', auth()->id())->delete();

        return $this->success(null, 'Cart cleared');
    }

    public function count(): JsonResponse
    {
        $count = Cart::where('user_id', auth()->id())->sum('quantity');

        return $this->success(['count' => $count]);
    }
}
