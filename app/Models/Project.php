<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'customer_id', 'manager_id', 'budget',
        'progress', 'status', 'priority', 'start_date', 'deadline',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'start_date' => 'date',
            'deadline' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            $project->slug = $project->slug ?: Str::slug($project->name).'-'.Str::lower(Str::random(4));
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasAnyRole(['super-admin', 'project-manager', 'finance'])) {
            return $query;
        }


        return $query->where(function ($q) use ($user) {
            $q->where('manager_id', $user->id)
                ->orWhereHas('staff', fn ($s) => $s->where('users.id', $user->id));
        });
    }

    /**
     * Completion percentage derived from task counts.
     * Uses eagerly-loaded counts when present, otherwise queries.
     */
    public function getProgressAttribute(): int
    {
        $total = $this->tasks_count ?? $this->tasks()->count();

        if ($total === 0) {
            return $this->status === 'completed' ? 100 : 0;
        }

        $done = $this->done_tasks_count ?? $this->tasks()->where('status', 'done')->count();

        return (int) round($done / $total * 100);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            'on_hold' => 'On Hold',
            default => Str::headline($this->status),
        };
    }

    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'bg-lime-100 text-lime-800 ring-lime-200',
            'completed' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'on_hold' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'cancelled' => 'bg-rose-100 text-rose-800 ring-rose-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }

    public function getDaysUntilDeadlineAttribute(): ?int
    {
        return $this->deadline?->diffInDays(now(), false) !== null
            ? (int) now()->startOfDay()->diffInDays($this->deadline, false)
            : null;
    }

    public function getDeadlineBadgeAttribute(): string
    {
        $days = $this->days_until_deadline;

        if ($days === null) {
            return 'No deadline';
        }

        if ($days < 0) {
            return abs($days).'d overdue';
        }

        if ($days === 0) {
            return 'Due today';
        }

        return 'Due in '.$days.' days';
    }
}
