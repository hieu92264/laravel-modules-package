<?php

namespace HieuDev92264\LaravelModules\Traits;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait ApiResponse
{
    protected function apiResponse(
        mixed $metadata = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_OK,
        ?Throwable $exception = null
    ): JsonResponse {
        if ($statusCode < Response::HTTP_CONTINUE || $statusCode >= 600) {
            throw new InvalidArgumentException('The HTTP status code must be between 100 and 599.');
        }

        $payload = [
            'message' => $message ?? 'Success',
            'status_code' => $statusCode,
            'metadata' => $metadata,
            'path' => request()->getPathInfo(),
            'timestamp' => now()->toISOString(),
        ];

        if ($exception && config('app.debug')) {
            $payload['debug'] = [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $this->exceptionTrace($exception),
            ];
        }

        return response()->json($payload, $statusCode);
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function exceptionTrace(Throwable $exception): array
    {
        return array_map(static function (array $frame): array {
            return array_filter([
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'class' => $frame['class'] ?? null,
                'type' => $frame['type'] ?? null,
                'function' => $frame['function'] ?? null,
            ], static fn (mixed $value): bool => $value !== null);
        }, array_slice($exception->getTrace(), 0, 5));
    }

    protected function success(
        mixed $metadata = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return $this->apiResponse($metadata, $message, $statusCode);
    }

    protected function error(
        mixed $metadata = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        ?Throwable $exception = null
    ): JsonResponse {
        return $this->apiResponse(
            $metadata,
            $message ?? 'An error occurred',
            $statusCode,
            $exception
        );
    }
}
