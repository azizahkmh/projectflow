<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Task::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'project_id' => ['required', 'exists:projects,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:todo,in_progress,review,done'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'estimate_hours' => ['nullable', 'integer', 'between:1,999'],
        ];
    }
}
