<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'phone'           => 'required|string|max:20',
            'address'         => 'required|string|max:1000',
            'payment_method'  => 'required|in:cod,gcash,card',
            'change_for'      => 'nullable|numeric|min:0',
            'request_cutlery' => 'boolean',
            'notes'           => 'nullable|string|max:500',
        ];
    }
}
