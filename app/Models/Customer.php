<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'company_name', 'contact_name', 'email', 'phone',
        'website', 'address', 'city', 'country', 'status', 'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'prospect' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'inactive' => 'bg-slate-100 text-slate-600 ring-slate-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->company_name))
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');
    }
}
