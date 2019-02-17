<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;

/**
 * ResponseService formats API response content
 */
class ResponseService
{
    /**
     * Formats success response
     * @param object|array $data
     * @param int $code
     * @param string $message
     * @return array
     */
    public static function getSuccessResponse(
        $data,
        $message,
        $code = Response::HTTP_OK
    ): array {
        $responsePayload = [
            'code' => $code,
            'status' => 'success',
            'message' => $message ?? Response::$statusTexts[$code],
        ];

        if (!empty($data)) {
            $responsePayload['data'] = $data;
        }

        return $responsePayload;
    }

    /**
     * Formats error response
     * @param string|array $message
     * @param int $code
     * @return array
     */
    public static function getErrorResponse(
        $message,
        $code = Response::HTTP_BAD_REQUEST
    ): array {
        return [
            'code' => $code,
            'status' => 'error',
            'message' => $message ?? Response::$statusTexts[$code]
        ];
    }
}