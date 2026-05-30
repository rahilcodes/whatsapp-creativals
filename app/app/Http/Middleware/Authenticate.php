<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * IMPORTANT: We return a RELATIVE path '/login' instead of route('login').
     *
     * route('login') generates an absolute URL based on APP_URL (https://ichatup.com/login).
     * This would bounce users on panel.besurebot.com off to the wrong domain!
     *
     * A relative path lets the browser resolve it against whatever domain
     * the user is currently on — ichatup.com/login OR panel.besurebot.com/login.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : '/login';
    }
}
