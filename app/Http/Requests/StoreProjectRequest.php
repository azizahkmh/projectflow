<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Project::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'budget' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'progress' => ['nullable', 'integer', 'between:0,100'],
            'status' => ['required', 'in:planning,in_progress,on_hold,completed,cancelled'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
            'staff' => ['nullable', 'array'],
            'staff.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'deadline.after_or_equal' => 'The deadline must be on or after the start date.',
        ];
    }
}
