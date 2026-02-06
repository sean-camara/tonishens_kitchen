<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'payment_method',
        'change_for',
        'request_cutlery',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'change_for' => 'decimal:2',
            'request_cutlery' => 'boolean',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
