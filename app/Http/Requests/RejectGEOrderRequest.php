<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectGEOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('ge_order');

        return $order && $this->user()->can('approve', $order);
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'A rejection reason is required.',
            'rejection_reason.min'      => 'The rejection reason must be at least 5 characters.',
        ];
    }
}
