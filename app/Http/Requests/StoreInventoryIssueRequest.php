<?php

namespace App\Http\Requests;

use App\Models\InventoryIssue;
use App\Models\ItemSerial;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('issue-inventory');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([InventoryIssue::TYPE_CONSUMABLE, InventoryIssue::TYPE_CHECKOUT])],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'recipient_user_id' => ['nullable', 'integer', Rule::exists((new User)->getTable(), 'id')],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:2000'],
            'serial_ids' => ['nullable', 'array'],
            'serial_ids.*' => ['required', 'integer', 'distinct', Rule::exists((new ItemSerial)->getTable(), 'id')],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($this->filled('recipient_user_id') && $this->filled('recipient_name')) {
                $validator->errors()->add('recipient_name', 'Choose a user recipient or enter a name, not both.');
            }
        }];
    }
}
