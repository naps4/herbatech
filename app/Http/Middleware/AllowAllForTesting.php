<?php
// app/Http/Middleware/AllowAllForTesting.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowAllForTesting
{
    public function handle(Request $request, Closure $next)
    {
        // Bypass semua authorization untuk testing
        return $next($request);
    }
}