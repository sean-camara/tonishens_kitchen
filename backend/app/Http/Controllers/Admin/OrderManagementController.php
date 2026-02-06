<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('user:id,first_name,last_name');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->latest()->get();

        return $this->success($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load('user:id,first_name,last_name,email,phone', 'items.dish', 'details', 'feedback.user');

        return $this->success($order);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:Pending,Preparing,On the Way,Completed,Canceled',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Notify customer
        Notification::create([
            'user_id' => $order->user_id,
            'type'    => 'order_status',
            'title'   => "Order #{$order->id} {$request->status}",
            'message' => "Your order status has been updated from {$oldStatus} to {$request->status}",
            'data'    => ['order_id' => $order->id, 'status' => $request->status],
        ]);

        return $this->success($order, 'Status updated');
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:Pending,Preparing,On the Way,Completed,Canceled',
        ]);

        Order::whereIn('id', $request->order_ids)->update(['status' => $request->status]);

        // Notify each customer
        $orders = Order::whereIn('id', $request->order_ids)->get();
        foreach ($orders as $order) {
            Notification::create([
                'user_id' => $order->user_id,
                'type'    => 'order_status',
                'title'   => "Order #{$order->id} {$request->status}",
                'message' => "Your order status has been updated to {$request->status}",
                'data'    => ['order_id' => $order->id, 'status' => $request->status],
            ]);
        }

        return $this->success(null, count($request->order_ids) . ' orders updated');
    }
}
