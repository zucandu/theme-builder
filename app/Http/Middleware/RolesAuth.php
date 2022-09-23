<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Deepplusplus\Admin\Role;

class RolesAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // get user role permissions
        $role = Role::findOrFail(auth()->user()->role_id);
        $permissions = $role->permissions;

        // get requested action
        $actionName = class_basename($request->route()->getActionname());

        // check if requested action is in permissions list
        foreach ($permissions as $permission)
        {
            $_namespaces_chunks = explode('\\', $permission->controller);
            $controller = end($_namespaces_chunks);
            if ($actionName === $controller . '@' . $permission->method)
            {
                // authorized request
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthorized Admin Role.'], 403);
    }
}
