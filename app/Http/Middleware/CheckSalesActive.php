<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSalesActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if (!$user->is_active) {
                $user->tokens()->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin untuk aktivasi kembali.',
                    'code'    => 'ACCOUNT_INACTIVE' 
                ], 403);
            }
        }

        return $next($request);
    }
}