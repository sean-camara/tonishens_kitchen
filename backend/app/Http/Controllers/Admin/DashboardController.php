<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Feedback;
use App\Models\Ingredient;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalSales = Order::where('status', 'Completed')->sum('total_amount');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'Pending')->count();

        // Best seller
        $bestSeller = OrderItem::select('dish_name', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('dish_name')
            ->orderByDesc('total_sold')
            ->first();

        // Recent orders
        $recentOrders = Order::with('user:id,first_name,last_name')
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'total_amount', 'status', 'created_at']);

        // Low stock
        $lowStock = Ingredient::with('category')
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->take(5)
            ->get();

        // Latest feedback
        $latestFeedback = Feedback::with('user:id,first_name,last_name')
            ->latest()
            ->first();

        return $this->success([
            'total_sales'     => $totalSales,
            'total_orders'    => $totalOrders,
            'pending_orders'  => $pendingOrders,
            'best_seller'     => $bestSeller?->dish_name,
            'recent_orders'   => $recentOrders,
            'low_stock'       => $lowStock,
            'latest_feedback' => $latestFeedback ? [
                'user_name' => $latestFeedback->user?->full_name,
                'rating'    => $latestFeedback->rating,
                'comment'   => $latestFeedback->comment,
            ] : null,
        ]);
    }
}
