<?php

/**
 * Class GetresponseApiException
 */
class GetresponseApiException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     * @return GetresponseApiException
     */
    public static function create_for_invalid_curl_response($message, $code)
    {
        return new self('CURL Error: ' . $message, $code);
    }
}
