<?php

use Illuminate\Http\JsonResponse;

if (!function_exists('result')) {
    /**
     * Build an application API response. Request auditing belongs to LogRequests;
     * request/response payloads are never logged here, regardless of suppression.
     * The final argument carries pagination or Laravel validation compatibility metadata.
     */
    function result($message, $data = [], $status = 200, $code = 0, $suppress_logging = false, array $additional = []): JsonResponse
    {
        $error = !(($status >= 200 && $status < 300) || $status === -1);
        $status = $status === -1 ? 200 : $status;

        return response()->json([
            'status' => $status,
            'response_code' => $code,
            'error' => $error,
            'message' => $message,
            // Keep the data contract stable for empty lists and falsy values.
            'data' => $data,
        ] + array_intersect_key($additional, array_flip(['links', 'meta', 'errors'])), $status);
    }
}
