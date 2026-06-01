<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            if (app()->environment(['local', 'testing'])) {
                Route::post('/admin/dev-reset', [App\Http\Controllers\Admin\AdminController::class, 'resetDatabase'])
                    ->middleware('auth')
                    ->name('admin.dev.reset');
            }
        },
    )
    ->withCommands([
        \App\Console\Commands\MigrateUploadedImages::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $renderError = function (Request $request, int $status, string $title, string $message, ?Throwable $exception = null) {
            if ($status >= 500 && $exception) {
                Log::error($message, [
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                    'path' => $request->path(),
                    'user_id' => $request->user()?->id,
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'title' => $title,
                    'status' => $status,
                ], $status);
            }

            return response()->view("errors.{$status}", [
                'title' => $title,
                'message' => $message,
                'status' => $status,
            ], $status);
        };

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please sign in again before continuing.',
                    'title' => 'Sign in required',
                    'status' => 401,
                ], 401);
            }

            return redirect()->guest(route('login'))
                ->with('warning', 'Please sign in before continuing. Your previous session may have ended.');
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($renderError) {
            return $renderError(
                $request,
                403,
                'This area is restricted',
                'You do not have permission to open this page or perform this action.',
                $exception
            );
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) use ($renderError) {
            return $renderError(
                $request,
                419,
                'Your session expired',
                'For your safety, this form expired. Please refresh the page and try again.',
                $exception
            );
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please fix the highlighted fields and try again.',
                    'title' => 'Some details need attention',
                    'status' => 422,
                    'errors' => $exception->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors($exception->errors())
                ->with('error', 'Please fix the highlighted fields and try again.');
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($renderError) {
            return $renderError(
                $request,
                404,
                'We could not find that page',
                'The page or record you requested may have been moved, deleted, or mistyped.',
                $exception
            );
        });

        $exceptions->render(function (Throwable $exception, Request $request) use ($renderError) {
            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            if (! in_array($status, [403, 404, 419, 422], true)) {
                $status = 500;
            }

            $messages = [
                403 => ['This area is restricted', 'You do not have permission to open this page or perform this action.'],
                404 => ['We could not find that page', 'The page or record you requested may have been moved, deleted, or mistyped.'],
                419 => ['Your session expired', 'For your safety, this form expired. Please refresh the page and try again.'],
                422 => ['Some details need attention', 'Please fix the highlighted fields and try again.'],
                500 => ['Something went wrong', 'Something unexpected happened on our side. Please try again in a moment.'],
            ];

            return $renderError($request, $status, $messages[$status][0], $messages[$status][1], $exception);
        });
    })->create();
