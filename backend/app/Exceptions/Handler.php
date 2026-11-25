<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /** @var array<int, class-string<Throwable>> */
    protected $dontReport = [
        DomainException::class,
    ];

    /** @var array<int, string> */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * @param  Request  $request
     */
    public function render($request, Throwable $e): Response
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->renderApiException($e);
        }

        return parent::render($request, $e);
    }

    private function renderApiException(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return new JsonResponse([
                'message' => 'Los datos enviados no son válidos.',
                'code' => 'validation_error',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof AuthenticationException) {
            return new JsonResponse([
                'message' => 'No autenticado.',
                'code' => 'unauthenticated',
            ], 401);
        }

        if ($exception instanceof AuthorizationException) {
            return new JsonResponse([
                'message' => 'No tienes permisos para realizar esta acción.',
                'code' => 'forbidden',
            ], 403);
        }

        if (
            $exception instanceof ModelNotFoundException ||
            ($exception instanceof NotFoundHttpException && $exception->getPrevious() instanceof ModelNotFoundException)
        ) {
            return new JsonResponse([
                'message' => 'Recurso no encontrado.',
                'code' => 'not_found',
            ], 404);
        }

        if ($exception instanceof DomainException) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
                'code' => $exception->errorCode(),
            ], $exception->statusCode());
        }

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();

            return new JsonResponse([
                'message' => $exception->getMessage() !== '' ? $exception->getMessage() : 'Error HTTP.',
                'code' => $status === 404 ? 'not_found' : 'http_error',
            ], $status);
        }

        return new JsonResponse([
            'message' => 'Error interno del servidor.',
            'code' => 'server_error',
        ], 500);
    }
}
