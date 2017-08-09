<?php

namespace lroman242\LaravelRBAC\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * The attributes that are fillable via mass assignment.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * Permissions can belong to many users.
     *
     * @return Model
     */
    public function users()
    {
        return $this->belongsToMany(config('auth.model') ?: config('auth.providers.users.model'))->withTimestamps();
    }

    /**
     * Permissions can belong to many roles.
     *
     * @return Model
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Assigns the given role to the permission.
     *
     * @param int $roleId
     *
     * @return bool
     */
    public function assignRole($roleId = null)
    {
        $roles = $this->roles;
        if (!$roles->contains($roleId)) {
            return $this->roles()->attach($roleId);
        }

        return false;
    }

    /**
     * Revokes the given role from the permission.
     *
     * @param int $roleId
     *
     * @return bool
     */
    public function revokeRole($roleId = null)
    {
        return $this->roles()->detach($roleId);
    }

    /**
     * Syncs the given role(s) with the permission.
     *
     * @param array $roleIds
     *
     * @return bool
     */
    public function syncRoles(array $roleIds = [])
    {
        return $this->roles()->sync($roleIds);
    }

    /**
     * Revokes all roles from the permission.
     *
     * @return bool
     */
    public function revokeAllRoles()
    {
        return $this->roles()->detach();
    }
}