<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        // Admin sees admin notifications (user_id = null) plus their own
        $notifications = Notification::where(function ($q) {
            $q->whereNull('user_id')
              ->orWhere('user_id', auth()->id());
        })
        ->latest()
        ->take(50)
        ->get();

        return $this->success($notifications);
    }

    public function markRead(Notification $notification): JsonResponse
    {
        $notification->update(['read_at' => now()]);
        return $this->success(null, 'Marked as read');
    }

    public function markAllRead(): JsonResponse
    {
        Notification::where(function ($q) {
            $q->whereNull('user_id')
              ->orWhere('user_id', auth()->id());
        })
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

        return $this->success(null, 'All marked as read');
    }
}
