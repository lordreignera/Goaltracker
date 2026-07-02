<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            $message = 'Your session expired. Please refresh the page and try again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 419);
            }

            $redirect = $request->is('login')
                ? redirect()->route('login')
                : redirect()->back();

            return $redirect
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->withErrors(['session_expired' => $message]);
        });
    })->create();
