<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->success(
            Ingredient::with('category')->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150',
            'category_id'   => 'required|exists:ingredient_categories,id',
            'unit'          => 'required|string|max:30',
            'quantity'      => 'numeric|min:0',
            'reorder_level' => 'numeric|min:0',
            'cost_per_unit' => 'numeric|min:0',
        ]);

        $ingredient = Ingredient::create($data);

        return $this->success($ingredient->load('category'), 'Created', 201);
    }

    public function update(Request $request, Ingredient $ingredient): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'sometimes|string|max:150',
            'category_id'   => 'sometimes|exists:ingredient_categories,id',
            'unit'          => 'sometimes|string|max:30',
            'quantity'      => 'numeric|min:0',
            'reorder_level' => 'numeric|min:0',
            'cost_per_unit' => 'numeric|min:0',
        ]);

        $ingredient->update($data);

        return $this->success($ingredient->load('category'), 'Updated');
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $ingredient->delete();
        return $this->success(null, 'Deleted');
    }

    // Categories
    public function categories(): JsonResponse
    {
        return $this->success(IngredientCategory::orderBy('name')->get());
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100|unique:ingredient_categories,name']);
        $cat = IngredientCategory::create(['name' => $request->name]);

        return $this->success($cat, 'Created', 201);
    }
}
