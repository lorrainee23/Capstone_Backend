<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMultiGuard
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $userType = strtolower(class_basename($user));

        if (!in_array($userType, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: role not allowed'
            ], 403);
        }

        return $next($request);
    }
}