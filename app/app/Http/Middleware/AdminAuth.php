<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = config('services.admin.user');
        $pass = config('services.admin.password');

        // Check Basic Auth credentials
        if ($request->getUser() !== $user || $request->getPassword() !== $pass) {
            return response('Unauthorized. Please log in.', 401, [
                'WWW-Authenticate' => 'Basic realm="WhatsApp Admin Dashboard"'
            ]);
        }

        return $next($request);
    }
}
