<?php

namespace HieuDev92264\LaravelModules\Traits; // Đổi 'traits' thành 'Traits'

use Illuminate\Http\JsonResponse;
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
        $payload = [
            // Dùng text mặc định nếu người dùng không truyền message
            'message' => $message ?? 'Success',
            'status_code' => $statusCode,
            'metadata' => $metadata,
            'path' => request()->getPathInfo(),
            'timestamp' => now()->toDateTimeString(),
        ];

        // Chỉ cần config('app.debug') là đủ chuẩn trong Laravel
        if ($exception && config('app.debug')) {
            $payload['debug'] = [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                // Giới hạn Trace ở 5 cấp độ để tránh tràn bộ nhớ JSON
                'trace' => array_slice($exception->getTrace(), 0, 5),
            ];
        }

        return response()->json($payload, $statusCode);
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
