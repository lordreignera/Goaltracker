<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMultiFactorAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! config('security.require_mfa') || ! $user || $user->two_factor_confirmed_at) {
            return $next($request);
        }

        $message = 'Multi-factor authentication is required. Set it up in Account Settings before continuing.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'setup_url' => route('profile.show'),
            ], 423);
        }

        return redirect()
            ->route('profile.show')
            ->with('warning', $message);
    }
}
