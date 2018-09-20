<?php

/**
 * Class GetresponseApiException
 */
class GetresponseApiException extends \Exception
{
    /**
     * @param $error_message
     * @return GetresponseApiException
     */
    public static function create_for_invalid_curl_response($error_message)
    {
        return new self('CURL Error: ' . $error_message);
    }
}
