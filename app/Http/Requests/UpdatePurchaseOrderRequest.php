<?php

namespace App\Http\Requests;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('procurement'));
    }

    public function rules(): array
    {
        return [
            'po_number' => ['required', 'string', 'max:20', Rule::unique((new PurchaseOrder)->getTable(), 'po_number')->ignore($this->route('procurement'))],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receiving_person_name' => ['required', 'string', 'max:255'],
            'receiving_person_position' => ['required', 'string', 'max:255'],
            'receiving_person_branch' => ['required', 'string', 'max:255'],
            'receiving_person_phone' => ['nullable', 'string', 'max:50'],
            'receiving_person_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in([
                PurchaseOrder::STATUS_DRAFT,
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_BACKORDER,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                PurchaseOrder::STATUS_FULLY_RECEIVED,
                PurchaseOrder::STATUS_CANCELLED,
            ])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', Rule::exists((new PurchaseOrderItem)->getTable(), 'id')],
            'items.*.description' => ['required', 'string', 'max:255'],
        ];
    }
}
