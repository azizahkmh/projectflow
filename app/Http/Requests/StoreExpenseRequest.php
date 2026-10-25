<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.manage');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:software,hardware,contractors,operations,marketing,travel,other'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000000'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'status' => ['required', 'in:pending,approved,rejected,reimbursed'],
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
