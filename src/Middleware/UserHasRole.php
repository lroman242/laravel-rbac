<?php

namespace lroman242\LaravelRBAC\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class UserHasRole
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
     * Check user role.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $role
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (! $this->auth->user()->is($role)) {
            if ($request->ajax()) {
                if ($request->expectsJson()) {
                    return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
                }

                return response('Unauthorized.', 401);
            }

            return abort(401);
        }

        return $next($request);
    }
}
