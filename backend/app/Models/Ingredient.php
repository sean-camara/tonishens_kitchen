<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'unit',
        'quantity',
        'reorder_level',
        'cost_per_unit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'reorder_level' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(IngredientCategory::class, 'category_id');
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }
}
