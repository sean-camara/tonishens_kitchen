<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AboutController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DishManagementController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AboutManagementController;
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\NotificationController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/dishes', [DishController::class, 'index']);
Route::get('/dishes/{dish}', [DishController::class, 'show']);
Route::get('/categories', [DishController::class, 'categories']);
Route::get('/about', [AboutController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (JWT)
|--------------------------------------------------------------------------
*/

Route::middleware('auth.jwt')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{dishId}', [CartController::class, 'update']);
    Route::delete('/cart/{dishId}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::get('/cart/count', [CartController::class, 'count']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/feedback', [OrderController::class, 'submitFeedback']);

    /*
    |----------------------------------------------------------------------
    | Admin Routes
    |----------------------------------------------------------------------
    */

    Route::middleware('admin')->prefix('admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Dishes
        Route::get('/dishes', [DishManagementController::class, 'index']);
        Route::post('/dishes', [DishManagementController::class, 'store']);
        Route::put('/dishes/{dish}', [DishManagementController::class, 'update']);
        Route::post('/dishes/{dish}', [DishManagementController::class, 'update']); // FormData PUT compat
        Route::delete('/dishes/{dish}', [DishManagementController::class, 'destroy']);

        // Categories
        Route::get('/categories', [DishManagementController::class, 'categories']);
        Route::post('/categories', [DishManagementController::class, 'storeCategory']);
        Route::delete('/categories/{category}', [DishManagementController::class, 'destroyCategory']);

        // Orders
        Route::get('/orders', [OrderManagementController::class, 'index']);
        Route::get('/orders/{order}', [OrderManagementController::class, 'show']);
        Route::put('/orders/{order}/status', [OrderManagementController::class, 'updateStatus']);
        Route::post('/orders/bulk-update', [OrderManagementController::class, 'bulkUpdate']);

        // Inventory
        Route::get('/ingredients', [InventoryController::class, 'index']);
        Route::post('/ingredients', [InventoryController::class, 'store']);
        Route::put('/ingredients/{ingredient}', [InventoryController::class, 'update']);
        Route::delete('/ingredients/{ingredient}', [InventoryController::class, 'destroy']);
        Route::get('/ingredient-categories', [InventoryController::class, 'categories']);
        Route::post('/ingredient-categories', [InventoryController::class, 'storeCategory']);

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'salesReport']);
        Route::get('/reports/top-selling', [ReportController::class, 'topSelling']);
        Route::get('/reports/sales/export', [ReportController::class, 'exportSalesCsv']);
        Route::get('/reports/top-selling/export', [ReportController::class, 'exportTopSellingCsv']);

        // About CMS
        Route::get('/about', [AboutManagementController::class, 'index']);
        Route::put('/about/history', [AboutManagementController::class, 'updateHistory']);
        Route::post('/about/contacts', [AboutManagementController::class, 'storeContact']);
        Route::delete('/about/contacts/{contact}', [AboutManagementController::class, 'destroyContact']);
        Route::post('/about/social-links', [AboutManagementController::class, 'storeSocialLink']);
        Route::delete('/about/social-links/{socialLink}', [AboutManagementController::class, 'destroySocialLink']);
        Route::post('/about/faqs', [AboutManagementController::class, 'storeFaq']);
        Route::delete('/about/faqs/{faq}', [AboutManagementController::class, 'destroyFaq']);

        // Admin Accounts
        Route::get('/accounts', [AdminAccountController::class, 'index']);
        Route::post('/accounts', [AdminAccountController::class, 'store']);
        Route::delete('/accounts/{user}', [AdminAccountController::class, 'destroy']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    });
});
