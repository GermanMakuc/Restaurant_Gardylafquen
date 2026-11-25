<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class Handler
{
    public static function renderApiException(Throwable $exception): JsonResponse
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

        if ($exception instanceof AuthorizationException || $exception instanceof AccessDeniedHttpException) {
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
