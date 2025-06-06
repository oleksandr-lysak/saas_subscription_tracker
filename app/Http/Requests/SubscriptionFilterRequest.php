<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billing_frequency' => 'nullable|string|in:monthly,yearly,weekly',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'base_currency' => 'nullable|string|in:USD,EUR,UAH',
            'month' => 'nullable|integer|min:1|max:12',
        ];
    }
}
