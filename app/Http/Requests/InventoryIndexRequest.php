<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\ItemBranch;
use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', ItemBranch::class);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'branch' => ['nullable', 'integer', Rule::exists((new Branch)->getTable(), 'id')],
            'location' => ['nullable', 'integer', Rule::exists((new Location)->getTable(), 'id')],
            'category' => ['nullable', Rule::in(['Asset', 'Consumable'])],
            'status' => ['nullable', Rule::in(['in_stock', 'low_stock', 'out_of_stock'])],
        ];
    }
}
