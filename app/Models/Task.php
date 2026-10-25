<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'assignee_id', 'title', 'description', 'status',
        'priority', 'due_date', 'completed_at', 'estimate_hours',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasAnyRole(['super-admin', 'project-manager', 'finance'])) {
            return $query;
        }


        return $query->where('assignee_id', $user->id);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            default => Str::headline($this->status),
        };
    }

    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'done' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'in_progress' => 'bg-lime-100 text-lime-800 ring-lime-200',
            'review' => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }

    public function getPriorityClassesAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'text-rose-600',
            'high' => 'text-amber-600',
            'low' => 'text-slate-400',
            default => 'text-slate-600',
        };
    }
}
