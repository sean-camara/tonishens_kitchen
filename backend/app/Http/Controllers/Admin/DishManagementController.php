<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDishRequest;
use App\Models\Category;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DishManagementController extends Controller
{
    public function index(): JsonResponse
    {
        $dishes = Dish::with('category')->orderBy('name')->get();
        return $this->success($dishes);
    }

    public function store(StoreDishRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('dishes', 'public');
        }

        $dish = Dish::create($data);

        return $this->success($dish->load('category'), 'Dish created', 201);
    }

    public function update(StoreDishRequest $request, Dish $dish): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($dish->image_path) {
                Storage::disk('public')->delete($dish->image_path);
            }
            $data['image_path'] = $request->file('image')->store('dishes', 'public');
        }

        $dish->update($data);

        return $this->success($dish->load('category'), 'Dish updated');
    }

    public function destroy(Dish $dish): JsonResponse
    {
        if ($dish->image_path) {
            Storage::disk('public')->delete($dish->image_path);
        }

        $dish->delete();

        return $this->success(null, 'Dish deleted');
    }

    // Categories
    public function categories(): JsonResponse
    {
        return $this->success(Category::orderBy('sort_order')->get());
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100|unique:categories,name']);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return $this->success($category, 'Category created', 201);
    }

    public function destroyCategory(Category $category): JsonResponse
    {
        if ($category->dishes()->exists()) {
            return $this->error('Category has dishes. Move or delete them first.', 422);
        }

        $category->delete();

        return $this->success(null, 'Category deleted');
    }
}
