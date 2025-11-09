<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'status',
        'profile_picture',
        'full_address'
    ];

    const ADMIN = 1;
    const STAFF = 2;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * User has many permissions through roles
     */
    public function permissions()
    {
        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->unique('id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($roleSlug)
    {
        return $this->roles->pluck('slug')->contains($roleSlug);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permissionSlug)
    {
        return $this->permissions()->pluck('slug')->contains($permissionSlug);
    }
}
