<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGEOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('ge_order');

        return $order && $this->user()->can('update', $order);
    }

    public function rules(): array
    {
        $order = $this->route('ge_order');

        return [
            'inventory_flag' => ['required', Rule::in(['STOCK', 'NONSTOCK'])],
            'po_number'      => ['nullable', 'string', 'max:50'],
            'supplier_id'    => ['required', 'exists:suppliers,id'],
            'user_id'        => ['required', 'exists:users,id'],
            'order_date'     => ['required', 'date'],
            'branch_id'      => ['required', 'exists:branches,id'],
            'account_code'   => ['required', 'string', 'max:50'],
            'description'    => ['required', 'string', 'max:500'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'action'         => ['required', Rule::in(['save_draft', 'submit'])],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.item_id'      => ['nullable', 'integer'],
            'items.*.item_id_text' => ['nullable', 'string', 'max:100'],
            'items.*.description'  => ['required', 'string', 'max:255'],
            'items.*.unit'         => ['nullable', 'string', 'max:30'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'items.*.total'        => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one order item is required.',
            'items.*.description.required' => 'Each item needs a description.',
            'items.*.quantity.required'    => 'Each item needs a quantity.',
            'items.*.quantity.min'         => 'Quantity must be greater than zero.',
            'items.*.unit_price.required'  => 'Each item needs a unit price.',
        ];
    }
}
