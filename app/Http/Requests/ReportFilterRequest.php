<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'branch_id' => ['nullable', 'integer', Rule::exists((new Branch)->getTable(), 'id')],
            'supplier_id' => ['nullable', 'integer', Rule::exists((new Supplier)->getTable(), 'id')],
        ];
    }
}
