<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DishController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Dish::with('category')
            ->where('is_available', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $dishes = $query->orderBy('name')->get();

        return $this->success($dishes);
    }

    public function show(Dish $dish): JsonResponse
    {
        $dish->load('category', 'feedback.user');
        $dish->avg_rating = $dish->averageRating;

        return $this->success($dish);
    }

    public function categories(): JsonResponse
    {
        return $this->success(
            Category::withCount('dishes')
                ->orderBy('sort_order')
                ->get()
        );
    }
}
