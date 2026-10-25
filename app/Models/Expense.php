<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'user_id', 'category', 'title', 'amount', 'status', 'spent_on', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_on' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'pending' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'rejected' => 'bg-rose-100 text-rose-800 ring-rose-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }
}
