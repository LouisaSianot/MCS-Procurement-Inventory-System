<?php

namespace App\Http\Requests;

use App\Models\ItemSerial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryIssueReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('issue-inventory');
    }

    public function rules(): array
    {
        return [
            'quantity' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'serial_ids' => ['nullable', 'array'],
            'serial_ids.*' => ['required', 'integer', 'distinct', Rule::exists((new ItemSerial)->getTable(), 'id')],
        ];
    }
}
