<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        if ($request->is('admin/*') && \Illuminate\Support\Facades\Route::has('admin.login')) {
            return route('admin.login');
        }

        return \Illuminate\Support\Facades\Route::has('login') ? route('login') : null;
    }
}
