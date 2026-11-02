<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        if ($request->user()->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk customer'
            ], 403);
        }

        return $next($request);
    }
}