<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'billing_frequency' => 'required|string|in:monthly,yearly,weekly',
            'currency' => 'required|string|in:USD,EUR,UAH',
            'start_date' => 'required|date',
            'description' => 'nullable|string',
        ];
    }
}
