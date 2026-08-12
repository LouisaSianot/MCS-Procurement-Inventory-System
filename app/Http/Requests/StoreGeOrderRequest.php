<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGeOrderRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'inventory_flag' => ['required', 'in:STOCK,NON-STOCK'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'date' => ['required', 'date'],
            'branch' => ['required', 'string', 'max:255'],
            'account_code' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['nullable', 'exists:items,id'],
            'items.*.item_description' => ['required', 'string', 'max:255'],
            'items.*.uom' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
}
