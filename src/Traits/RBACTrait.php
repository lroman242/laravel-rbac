<?php
namespace lroman242\LaravelRBAC\Traits;

use Illuminate\Database\Eloquent\Model;
use lroman242\LaravelRBAC\Models\Permission;
use lroman242\LaravelRBAC\Models\Role;

trait RBACTrait
{
	/*
	|----------------------------------------------------------------------
	| Role Trait Methods
	|----------------------------------------------------------------------
	|
	*/

	/**
	 * Users can have many roles.
	 *
	 * @return Model
	 */
	public function roles()
	{
		return $this->belongsToMany(Role::class)->withTimestamps();
	}

	/**
	 * Get all user roles.
	 *
	 * @return array|null
	 */
	public function getRoles()
	{
		if (! is_null($this->roles)) {
			return $this->roles->lists('slug')->all();
		}

		return null;
	}

	/**
	 * Checks if the user has the given role.
	 *
	 * @param  string $slug
	 * @return bool
	 */
	public function is($slug)
	{
		$slug = strtolower($slug);

		foreach ($this->roles as $role) {
			if ($role->slug == $slug) return true;
		}

		return false;
	}

	/**
	 * Assigns the given role to the user.
	 *
	 * @param  int $roleId
	 * @return bool
	 */
	public function assignRole($roleId = null)
	{
		$roles = $this->roles;

		if (! $roles->contains($roleId)) {
			return $this->roles()->attach($roleId);
		}

		return false;
	}

	/**
	 * Revokes the given role from the user.
	 *
	 * @param  int $roleId
	 * @return bool
	 */
	public function revokeRole($roleId = null)
	{
		return $this->roles()->detach($roleId);
	}

	/**
	 * Syncs the given role(s) with the user.
	 *
	 * @param  array $roleIds
	 * @return bool
	 */
	public function syncRoles(array $roleIds)
	{
		return $this->roles()->sync($roleIds);
	}

	/**
	 * Revokes all roles from the user.
	 *
	 * @return bool
	 */
	public function revokeAllRoles()
	{
		return $this->roles()->detach();
	}

	/*
	|----------------------------------------------------------------------
	| Permission Trait Methods
	|----------------------------------------------------------------------
	|
	*/

    /**
     * Users can have many permissions.
     *
     * @return Model
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

	/**
	 * Get all user role permissions.
	 *
	 * @return array|null
	 */
	public function getPermissions()
	{
		$permissions = [[], []];

		foreach ($this->roles as $role) {
			$permissions[] = $role->getPermissions();
		}

		$permissions[] = $this->permissions->pluck('slug')->all();

		return call_user_func_array('array_merge', $permissions);
	}

	/**
	 * Check if user has the given permission.
	 *
	 * @param  string $permission
	 * @param array $arguments
	 * @return bool
	 */
	public function can($permission, $arguments = [])
	{
		$can = false;

		foreach ($this->roles as $role) {
			if ($role->special === 'no-access') {
				return false;
			}

			if ($role->special === 'all-access') {
				return true;
			}

			if ($role->can($permission)) {
				$can = true;
			}
		}

		if (!$can) {
		    $can = $this->permissions()->where('slug', '=', $permission)->exists();
        }

		return $can;
	}

	/**
	 * Check if user has at least one of the given permissions
	 *
	 * @param  array $permissions
	 * @return bool
	 */
	public function canAtLeast(array $permissions)
	{
		$can = false;

		foreach ($this->roles as $role) {
			if ($role->special === 'no-access') {
				return false;
			}

			if ($role->special === 'all-access') {
				return true;
			}

			if ($role->canAtLeast($permissions)) {
				$can = true;
			}
		}

        if (!$can) {
            $can = $this->permissions()->whereIn('slug', $permissions)->exists();
        }

		return $can;
	}

    /**
     * Assigns the given permission to the user.
     *
     * @param  int $permissionId
     * @return bool
     */
    public function assignPermission($permissionId = null)
    {
        $permissions = $this->permissions;

        if (! $permissions->contains($permissionId)) {
            return $this->permissions()->attach($permissionId);
        }

        return false;
    }

    /**
     * Revokes the given permission from the user.
     *
     * @param  int $permissionId
     * @return bool
     */
    public function revokePermission($permissionId = null)
    {
        return $this->permissions()->detach($permissionId);
    }

    /**
     * Syncs the given permission(s) with the user.
     *
     * @param  array $permissionIds
     * @return bool
     */
    public function syncPermissions(array $permissionIds)
    {
        return $this->permissions()->sync($permissionIds);
    }

    /**
     * Revokes all permissions from the user.
     *
     * @return bool
     */
    public function revokeAllPermissions()
    {
        return $this->permissions()->detach();
    }


    /*
    |----------------------------------------------------------------------
    | Magic Methods
    |----------------------------------------------------------------------
    |
    */

	/**
	 * Magic __call method to handle dynamic methods.
	 *
	 * @param  string $method
	 * @param  array  $arguments
	 * @return mixed
	 */
	public function __call($method, $arguments = array())
	{
		// Handle isRoleslug() methods
		if (starts_with($method, 'is') and $method !== 'is') {
			$role = substr($method, 2);

			return $this->is($role);
		}

		// Handle canDoSomething() methods
		if (starts_with($method, 'can') and $method !== 'can') {
			$permission = substr($method, 3);

			return $this->can($permission);
		}

		return parent::__call($method, $arguments);
	}
}
