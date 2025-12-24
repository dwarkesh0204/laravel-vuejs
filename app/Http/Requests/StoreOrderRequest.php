<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'symbol' => ['required', 'string', 'max:10', 'alpha'],
            'side' => ['required', Rule::in([Order::SIDE_BUY, Order::SIDE_SELL])],
            'price' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,8})?$/'],
            'amount' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,8})?$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'symbol.required' => 'The trading symbol is required.',
            'symbol.alpha' => 'The trading symbol must only contain letters.',
            'side.required' => 'The order side (buy/sell) is required.',
            'side.in' => 'The order side must be either "buy" or "sell".',
            'price.required' => 'The price is required.',
            'price.gt' => 'The price must be greater than zero.',
            'price.regex' => 'The price can have up to 8 decimal places.',
            'amount.required' => 'The amount is required.',
            'amount.gt' => 'The amount must be greater than zero.',
            'amount.regex' => 'The amount can have up to 8 decimal places.',
        ];
    }
}


