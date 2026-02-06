<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Feedback;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items')
            ->latest()
            ->get();

        return $this->success($orders);
    }

    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return $this->error('Forbidden', 403);
        }

        $order->load('items.dish', 'details', 'feedback');

        return $this->success($order);
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $cartItems = Cart::with('dish')
            ->where('user_id', auth()->id())
            ->get();

        if ($cartItems->isEmpty()) {
            return $this->error('Cart is empty', 422);
        }

        // Verify all dishes are available
        foreach ($cartItems as $item) {
            if (!$item->dish || !$item->dish->is_available) {
                return $this->error("'{$item->dish?->name}' is no longer available", 422);
            }
        }

        $order = DB::transaction(function () use ($request, $cartItems) {
            $subtotal = $cartItems->sum(fn ($i) => $i->dish->price * $i->quantity);
            $taxRate = 0.12;
            $taxAmount = round($subtotal * $taxRate, 2);
            $deliveryFee = 50.00;
            $totalAmount = $subtotal + $taxAmount + $deliveryFee;

            $order = Order::create([
                'user_id'      => auth()->id(),
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'status'       => 'Pending',
            ]);

            // Create order items (snapshot prices)
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'dish_id'    => $item->dish_id,
                    'dish_name'  => $item->dish->name,
                    'unit_price' => $item->dish->price,
                    'quantity'   => $item->quantity,
                    'line_total' => $item->dish->price * $item->quantity,
                ]);
            }

            // Create order details
            $order->details()->create($request->validated());

            // Clear cart
            Cart::where('user_id', auth()->id())->delete();

            // Create notification for admins
            Notification::create([
                'user_id' => null, // admin notification
                'type'    => 'new_order',
                'title'   => 'New Order #' . $order->id,
                'message' => auth()->user()->full_name . ' placed a new order',
                'data'    => ['order_id' => $order->id],
            ]);

            return $order;
        });

        $order->load('items', 'details');

        return $this->success($order, 'Order placed successfully', 201);
    }

    public function cancel(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return $this->error('Forbidden', 403);
        }

        if ($order->status !== 'Pending') {
            return $this->error('Only pending orders can be canceled', 422);
        }

        $order->update(['status' => 'Canceled']);

        return $this->success($order, 'Order canceled');
    }

    public function submitFeedback(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return $this->error('Forbidden', 403);
        }

        if ($order->status !== 'Completed') {
            return $this->error('Can only review completed orders', 422);
        }

        $request->validate([
            'ratings'           => 'required|array|min:1',
            'ratings.*.dish_id' => 'required|exists:dishes,id',
            'ratings.*.rating'  => 'required|integer|min:1|max:5',
            'ratings.*.comment' => 'nullable|string|max:1000',
        ]);

        foreach ($request->ratings as $r) {
            Feedback::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'dish_id'  => $r['dish_id'],
                ],
                [
                    'user_id' => auth()->id(),
                    'rating'  => $r['rating'],
                    'comment' => $r['comment'] ?? null,
                ]
            );
        }

        return $this->success(null, 'Feedback submitted');
    }
}
