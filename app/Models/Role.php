<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name','slug'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Role has many permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Check if role has a permission
     */
    public function hasPermission($permissionSlug)
    {
        return $this->permissions->pluck('slug')->contains($permissionSlug);
    }
}
