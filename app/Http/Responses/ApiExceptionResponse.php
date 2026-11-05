<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ApiExceptionResponse
{
    public static function render(Response $response, Throwable $exception, Request $request): Response
    {
        // OAuth2 error bodies and browser redirects/forms have their own contracts.
        if (!$request->is('api/*') || $request->is('api/v1/oauth', 'api/v1/oauth/*') || $response->getStatusCode() < 400) {
            return $response;
        }
        $status = $response->getStatusCode();
        $body = json_decode($response->getContent(), true);
        $errors = $exception instanceof ValidationException ? $exception->errors() : ($body['errors'] ?? []);
        $message = $status === 422 ? 'Validation error' : ($status >= 500 ? 'Server error' : ($body['message'] ?? Response::$statusTexts[$status] ?? 'Request failed'));
        $result = result($message, $errors, $status, 0, true, $errors ? ['errors' => $errors] : []);
        // Preserve rate-limit, authentication, CORS, and other middleware headers.
        foreach ($response->headers->all() as $name => $values) {
            if (!in_array(strtolower($name), ['content-type', 'content-length'], true)) {
                $result->headers->set($name, $values);
            }
        }
        return $result;
    }
}
