<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, string>
     */
    protected $dontReport = [
        DomainException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @return Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->renderApiException($exception);
        }

        return parent::render($request, $exception);
    }

    private function renderApiException(Exception $exception): JsonResponse
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

        if ($exception instanceof ModelNotFoundException) {
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
                'message' => $status === 404 ? 'Ruta o recurso no encontrado.' : ($exception->getMessage() ?: 'Error en la solicitud.'),
                'code' => $status === 404 ? 'not_found' : 'http_error',
            ], $status);
        }

        $debug = (bool) config('app.debug');

        return new JsonResponse([
            'message' => $debug ? $exception->getMessage() : 'Error interno del servidor.',
            'code' => 'internal_error',
        ], 500);
    }
}
