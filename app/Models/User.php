<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const OWNER = 'owner';
    public const ADMIN = 'admin';
    public const TENANT = 'tenant';

    /** In-memory defaults so freshly created models match the DB defaults. */
    protected $attributes = ['role' => self::TENANT, 'is_active' => true];

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_users')->withTimestamps();
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [self::OWNER, self::ADMIN], true);
    }
}
