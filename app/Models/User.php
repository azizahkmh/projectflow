<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /** The canonical role slugs. */
    public const ROLES = [
        'super-admin' => 'Super Admin / Owner',
        'project-manager' => 'Project Manager',
        'staff' => 'Staff / Employee',
        'finance' => 'Finance / Accountant',
    ];

    protected $fillable = [
        'name', 'email', 'password', 'job_title', 'phone',
        'avatar_color', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function projectsAsManager(): HasMany
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function customerProfile(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');
    }

    public function getPrimaryRoleLabelAttribute(): string
    {
        $role = $this->roles->first();

        return $role ? (self::ROLES[$role->name] ?? str($role->name)->headline()->toString()) : 'No role';
    }
}
