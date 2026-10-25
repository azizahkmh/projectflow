<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'period_start', 'period_end', 'status', 'generated_by', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'generated_at' => 'datetime',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'ready' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'processing' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'failed' => 'bg-rose-100 text-rose-800 ring-rose-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }
}
