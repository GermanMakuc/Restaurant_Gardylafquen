<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class Cors
{
    /**
     * Restrict CORS to configured frontend origin(s) per AGENTS.md §7.5.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');
        $allowedOrigins = $this->allowedOrigins();
        $allowOrigin = ($origin !== null && in_array($origin, $allowedOrigins, true)) ? $origin : null;

        if ($request->isMethod('OPTIONS')) {
            $response = new Response('', 204);
        } else {
            /** @var Response $response */
            $response = $next($request);
        }

        if ($allowOrigin !== null) {
            $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With');
            $response->headers->set('Vary', 'Origin');
        }

        return $response;
    }

    /**
     * @return array<int, string>
     */
    private function allowedOrigins(): array
    {
        $raw = (string) config('app.cors_allowed_origins', 'http://localhost:4321');
        $items = array_map('trim', explode(',', $raw));

        return array_values(array_filter($items, static function (string $item): bool {
            return $item !== '';
        }));
    }
}
