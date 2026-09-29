<?php

namespace App\Http\Requests;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('location'));
    }

    public function rules(): array
    {
        $location = $this->route('location');

        return [
            'branch_id' => ['required', Rule::exists((new Branch)->getTable(), 'id')],
            'name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->where('branch_id', $this->input('branch_id'))->ignore($location->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
