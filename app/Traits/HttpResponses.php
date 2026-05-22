<?php

namespace App\Traits;

trait HttpResponses
{
    protected function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }
    protected function pagedSuccess($data, array $meta = [], $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => array_merge([
                'current_page' => null,
                'per_page' => null,
                'total' => null,
                'last_page' => null,
            ], $meta),
        ], $code);
    }

    protected function error($data, $message = null, $code = 422)
    {
        return response()->json([
            'success' => false,
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function handleException(\Throwable $exception, string $message = 'An error occurred.')
    {
        report($exception);

        return $this->error(null, $message, 500);
    }
}
