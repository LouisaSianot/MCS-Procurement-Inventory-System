<?php

namespace App\Http\Requests;

use App\Models\PurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PurchaseOrder::class);
    }

    public function rules(): array
    {
        return [
            'po_number' => ['nullable', 'string', 'max:20'],
            'ge_order_id' => ['required', 'integer', 'exists:ge_orders,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receiving_person_name' => ['required', 'string', 'max:255'],
            'receiving_person_position' => ['required', 'string', 'max:255'],
            'receiving_person_branch' => ['required', 'string', 'max:255'],
            'receiving_person_phone' => ['nullable', 'string', 'max:50'],
            'receiving_person_email' => ['nullable', 'email', 'max:255'],
            'action' => ['required', Rule::in(['save_draft', 'place_order'])],
        ];
    }
}
