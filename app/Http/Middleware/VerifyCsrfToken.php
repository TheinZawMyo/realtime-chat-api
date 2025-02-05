<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'api/*',
    ];
    protected function tokensMatch($request)
    {
        if (!$request->hasSession()) {
            Log::error('Session store not set on request.');
            return false;
        }
        $token = $request->header('X-XSRF-TOKEN') ?: $request->input('_token');
        $sessionToken = $request->session()->token();

        Log::info('CSRF Token from request: ' . ($token ?? 'null'));
        Log::info('CSRF Token from session: ' . ($sessionToken ?? 'null'));

        return parent::tokensMatch($request);
    }
}