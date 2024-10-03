<?php

namespace Alireza10up\WordpressPlus\Http;

abstract class BaseController
{
    /**
     * send json success response
     *
     * @param mixed $message
     * @param int $statusCode
     * @return void
     */
    protected function jsonSuccessResponse(mixed $message, int $statusCode): void
    {
        wp_send_json_success($message, $statusCode);
    }

    /**
     * send json error response
     *
     * @param mixed $message
     * @param int $statusCode
     * @return void
     */
    protected function jsonErrorResponse(mixed $message, int $statusCode): void
    {
        wp_send_json_error($message, $statusCode);
    }

    /**
     * render view
     *
     * @param string $name
     * @param array $data
     * @return void
     */
    protected function view(string $name, array $data): void
    {
        extract($data);

        $path = str_replace('.', '/', $name);

        include_once __DIR__ . DIRECTORY_SEPARATOR . '/../' . DIRECTORY_SEPARATOR . $path . '.php';
    }
}