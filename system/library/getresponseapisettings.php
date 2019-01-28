<?php

class GetresponseApiSettings
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $domain;
    /** @var string */
    private $url;

    const API_KEY_FIELD_NAME = 'getresponse_apikey';
    const API_URL_FIELD_NAME = 'getresponse_api_url';
    const API_DOMAIN_FIELD_NAME = 'getresponse_api_domain';

    private static $availableEnvironments = [
        'smb' => [
            'name' => 'Getresponse',
            'url' => 'https://api.getresponse.com/v3',
            'is_mx' => false
        ],
        'mxus' => [
            'name' => 'GetResponse Enterprise COM',
            'url' => 'https://api3.getresponse360.com/v3',
            'is_mx' => true
        ],
        'mxpl' => [
            'name' => 'GetResponse Enterprise PL',
            'url' => 'https://api3.getresponse360.pl/v3',
            'is_mx' => true
        ],
    ];

    /**
     * @param string $apiKey
     * @param string $domain
     * @param string $url
     */
    public function __construct($apiKey, $domain, $url)
    {
        $this->apiKey = $apiKey;
        $this->domain = $domain;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getHiddenApiKey()
    {
        return strlen($this->apiKey) > 0 ? str_repeat("*", strlen($this->apiKey) - 6).substr($this->apiKey, -6) : '';
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isMx()
    {
        foreach (self::$availableEnvironments as $env) {
            if ($env['url'] == $this->url) {
                return $env['is_mx'];
            }
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (empty($this->apiKey)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $post
     * @return GetresponseApiSettings
     */
    public static function createFromPost($post)
    {
        if (isset($post['getresponse-enterprise'])) {
            return new GetresponseApiSettings(
                $post[self::API_KEY_FIELD_NAME],
                $post[self::API_DOMAIN_FIELD_NAME],
                $post[self::API_URL_FIELD_NAME]
            );
        } else {
            return new GetresponseApiSettings(
                $post[self::API_KEY_FIELD_NAME],
                '',
                self::$availableEnvironments['smb']['url']
            );
        }
    }

    /**
     * @return array
     */
    public static function getAvailableEnvironments()
    {
        return self::$availableEnvironments;
    }

}