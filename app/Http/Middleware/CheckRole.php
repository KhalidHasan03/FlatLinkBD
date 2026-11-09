<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Required role: ' . implode(', ', $roles),
                ], 403);
            }

            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
