<?php

declare(strict_types=1);

use App\Exceptions\DomainException;
use App\Exceptions\Handler;
use App\Http\Middleware\Cors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->remove(HandleCors::class);
        $middleware->append(Cors::class);
        $middleware->redirectGuestsTo(static fn (): ?string => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            DomainException::class,
        ]);

        $exceptions->render(function (Throwable $e, Request $request): ?Response {
            if ($request->is('api/*') || $request->expectsJson()) {
                return Handler::renderApiException($e);
            }

            return null;
        });
    })
    ->create();
