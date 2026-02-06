<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function salesReport(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        [$from, $to] = $this->resolveDateRange($period);

        $orders = Order::where('status', 'Completed')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        // Chart data grouped by date
        $chartData = Order::where('status', 'Completed')
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw('DATE(created_at) as label'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return $this->success([
            'total_sales'  => $totalSales,
            'total_orders' => $totalOrders,
            'avg_order'    => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0,
            'chart_data'   => $chartData,
            'orders'       => $orders,
        ]);
    }

    public function topSelling(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        [$from, $to] = $this->resolveDateRange($period);

        $dishes = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'Completed')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select(
                'order_items.dish_name as name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('order_items.dish_name')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get();

        return $this->success([
            'dishes'     => $dishes,
            'chart_data' => $dishes,
        ]);
    }

    public function exportSalesCsv(Request $request): StreamedResponse
    {
        $period = $request->get('period', 'month');
        [$from, $to] = $this->resolveDateRange($period);

        $orders = Order::where('status', 'Completed')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order ID', 'Date', 'Status', 'Total']);
            foreach ($orders as $o) {
                fputcsv($handle, [$o->id, $o->created_at->format('Y-m-d H:i'), $o->status, $o->total_amount]);
            }
            fclose($handle);
        }, 'sales-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportTopSellingCsv(Request $request): StreamedResponse
    {
        $period = $request->get('period', 'month');
        [$from, $to] = $this->resolveDateRange($period);

        $dishes = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'Completed')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select(
                'order_items.dish_name as name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('order_items.dish_name')
            ->orderByDesc('total_sold')
            ->get();

        return response()->streamDownload(function () use ($dishes) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Dish', 'Total Sold', 'Revenue']);
            foreach ($dishes as $d) {
                fputcsv($handle, [$d->name, $d->total_sold, $d->revenue]);
            }
            fclose($handle);
        }, 'top-selling.csv', ['Content-Type' => 'text/csv']);
    }

    private function resolveDateRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week'  => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year'  => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->subYears(10), now()], // 'all'
        };
    }
}
