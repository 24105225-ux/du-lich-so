<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        if (
            !$user ||
            !in_array(
                $user->role,
                $roles,
                true
            )
        ) {
            Log::warning(
                'Unauthorized role access',
                [
                    'user_id' => $user?->id,
                    'role' => $user?->role,
                    'required_roles' => $roles,
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                ]
            );

            abort(
                403,
                'Không đủ quyền truy cập.'
            );
        }

        return $next($request);
    }
}
