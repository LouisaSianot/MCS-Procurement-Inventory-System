<?php

namespace App\Http\Requests;

use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PurchaseReceipt::class);
    }

    public function rules(): array
    {
        return [
            'receipt_number' => ['required', 'string', 'max:30', Rule::unique((new PurchaseReceipt)->getTable(), 'receipt_number')],
            'purchase_order_id' => ['required', Rule::exists((new PurchaseOrder)->getTable(), 'id')],
            'received_at' => ['required', 'date'],
            'supplier_delivery_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'integer', 'distinct', Rule::exists((new PurchaseOrderItem)->getTable(), 'id')],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
}
