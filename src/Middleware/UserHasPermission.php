<?php

namespace lroman242\LaravelRBAC\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use lroman242\LaravelRBAC\Models\Role;

class UserHasPermission
{
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new UserHasPermission instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Check user permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param array|string             $permissions
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        if ($this->auth->check()) {
            if (! $this->auth->user()->can($permissions)) {
                if ($request->ajax()) {
                    if ($request->expectsJson()) {
                        return response()->json(['status' => false, 'message' => 'Unauthorized action.'], 403);
                    }

                    return response('Unauthorized action.', 403);
                }

                abort(403, 'Unauthorized action.');
            }
        } else {
            $guest = Role::whereSlug('guest')->first();

            if ($guest) {
                if (! $guest->can($permissions)) {
                    if ($request->ajax()) {
                        if ($request->expectsJson()) {
                            return response()->json(['status' => false, 'message' => 'Unauthorized action.'], 403);
                        }

                        return response('Unauthorized action.', 403);
                    }

                    abort(403, 'Unauthorized action.');
                }
            }
        }


        return $next($request);
    }
}
