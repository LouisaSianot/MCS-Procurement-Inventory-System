<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Location::class);
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', Rule::exists((new Branch)->getTable(), 'id')],
            'name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->where('branch_id', $this->input('branch_id'))],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
