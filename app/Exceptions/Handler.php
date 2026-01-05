<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];


    /**
     * Determine if the request expects a JSON response (API or AJAX).
     */
    protected function isApi($request): bool
    {
        return $request->is('api/*') ||
            $request->expectsJson() ||
            str_contains(strtolower($request->header('accept')), 'application/json');
    }

    /**
     * Handle unauthenticated user for API and web.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->isApi($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }
        return redirect()->guest(route('login'));
    }

    public function render($request, Throwable $exception)
    {
        if ($this->isApi($request)) {
            // Spatie role/permission error
            if ($exception instanceof UnauthorizedException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: You do not have the required role or permission.',
                ], 403);
            }

            // Laravel authorization error
            if ($exception instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                ], 403);
            }

            // Validation error
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $exception->errors(),
                ], 422);
            }

            // HTTP exceptions (404, 403, etc.)
            if ($exception instanceof HttpException) {
                $status = $exception->getStatusCode();
                $msg = $status === 404 ? 'Resource not found' : ($exception->getMessage() ?: 'HTTP error');
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], $status);
            }

            // Authentication error (should be handled by unauthenticated, but fallback)
            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Fallback (500)
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $exception->getMessage() : 'Internal server error',
            ], 500);
        }
        return parent::render($request, $exception);
    }
}
