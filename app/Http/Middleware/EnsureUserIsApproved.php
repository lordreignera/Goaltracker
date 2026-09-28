<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isApproved()) {
            return $next($request);
        }

        $message = match (true) {
            ! $user->is_active => 'Your account is inactive. Please contact the administrator.',
            $user->approval_status === 'pending' => 'Your account is awaiting approval. Please contact the administrator.',
            default => 'Your account request was rejected. Please contact the administrator.',
        };

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()
            ->route('login')
            ->withErrors(['email' => $message]);
    }
}
