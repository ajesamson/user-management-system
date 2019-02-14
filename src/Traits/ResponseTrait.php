<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait
{
    /**
     * Composes a success response payload
     * @param $data
     * @param int $code
     * @return array
     */
    public static function successResponse($data, $code = Response::HTTP_OK): array
    {
        return [
            'code' => $code,
            'status' => 'success',
            'data' => $data
        ];
    }

    /**
     * Composes an error response payload
     * @param $message
     * @param int $code
     * @return array
     */
    public static function errorResponse($message, $code = Response::HTTP_BAD_REQUEST): array
    {
        return [
            'code' => $code,
            'status' => 'error',
            'message' => $message || Response::$statusTexts[$code]
        ];
    }
}