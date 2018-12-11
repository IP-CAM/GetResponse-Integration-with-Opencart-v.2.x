<?php
require_once('getresponseApiException.php');
/**
 * GetResponse API v3 client library
 *
 * @author GetResponse <grintegrations@getresponse.com>
 *
 * @see http://apidocs.getresponse.com/v3/resources
 * @see https://github.com/GetResponse/getresponse-api-php
 */
class GetResponseApiV3
{
    /** @var string */
    private $api_key;

    /** @var string */
    private $api_url = 'https://api.getresponse.com/v3';

    /** @var int */
    private $timeout = 10;

    /**
     * X-Domain header value if empty header will be not provided
     * @var string|null
     */
    private $enterprise_domain = null;

    /**
     * X-APP-ID header value if empty header will be not provided
     * @var string|null
     */
    private $app_id = 'e0b666d6-f97f-485b-8dd4-8b9be196963b';

    /** @var array */
    private $headers;

    /**
     * Set api key and optionally API endpoint
     * @param string      $api_key
     * @param null|string $api_url
     * @param null|string $domain
     */
    public function __construct($api_key, $api_url = null, $domain = null)
    {
        $this->api_key = $api_key;
        $this->enterprise_domain = $domain;

        if (!empty($api_url)) {
            $this->api_url = $api_url;
        }
    }

    /**
     * We can modify internal settings
     * @param $key
     * @param $value
     */
    function __set($key, $value)
    {
        $this->{$key} = $value;
    }

    /**
     * Return all campaigns
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getAccount()
    {
        $result = (array) $this->call('accounts');
        if (!isset($result['accountId'])) {
            throw new GetresponseApiException('Cannot load account details.');
        }
    }

    /**
     * Return all campaigns
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getCampaigns()
    {
        $params['perPage'] = 100;
        $params['page'] = 1;
        $result = (array) $this->call('campaigns?' . $this->setParams($params), 'GET', [], true);
        $headers = $this->getHeaders();

        for($i = 2; $i <= $headers['TotalPages']; $i++) {
            $params['page'] = $i;
            $res = (array) $this->call('campaigns?' . $this->setParams($params), 'GET', []);
            $result = array_merge($result, $res);
        }

        return $result;
    }

    /**
     * Return all autoresponders
     * @param null $params
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getAutoresponders($params = [])
    {
        $params['perPage'] = 100;
        $params['page'] = 1;
        $result = (array) $this->call('autoresponders?' . $this->setParams($params), 'GET', [], true);
        $headers = $this->getHeaders();

        for($i = 2; $i <= $headers['TotalPages']; $i++) {
            $params['page'] = $i;
            $res = (array) $this->call('autoresponders?' . $this->setParams($params), 'GET', []);
            $result = array_merge($result, $res);
        }

        return $result;
    }

    /**
     * add single contact into your campaign
     *
     * @param $params
     * @return mixed
     * @throws GetresponseApiException
     */
    public function addContact($params)
    {
        return $this->call('contacts', 'POST', $params);
    }

    /**
     * retrieving contact by params
     * @param array $params
     *
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getContacts($params = [])
    {
        return $this->call('contacts?' . $this->setParams($params));
    }

    /**
     * retrieve account custom fields
     * @param array $params
     *
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getCustomFields($params = [])
    {
        return $this->call('custom-fields?' . $this->setParams($params));
    }

    /**
     * add custom field
     *
     * @param $params
     * @return mixed
     * @throws GetresponseApiException
     */
    public function setCustomField($params)
    {
        return $this->call('custom-fields', 'POST', $params);
    }

    /**
     * get single web form
     *
     * @param int $w_id
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getWebForm($w_id)
    {
        return $this->call('webforms/' . $w_id);
    }

    /**
     * retrieve all webforms
     * @param array $params
     *
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getWebForms($params = [])
    {
        return $this->call('webforms?' . $this->setParams($params));
    }

    /**
     * get single form
     *
     * @param int $form_id
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getForm($form_id)
    {
        return $this->call('forms/' . $form_id);
    }

    /**
     * retrieve all forms
     * @param array $params
     *
     * @return mixed
     * @throws GetresponseApiException
     */
    public function getForms($params = [])
    {
        return $this->call('forms?' . $this->setParams($params));
    }

    /**
     * Curl run request
     *
     * @param null $api_method
     * @param string $http_method
     * @param array $params
     * @param bool $withHeaders
     * @return mixed
     * @throws GetresponseApiException
     */
    private function call($api_method = null, $http_method = 'GET', $params = [], $withHeaders = false)
    {
        $params = json_encode($params);
        $url = $this->api_url . '/' . $api_method;

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HEADER => $withHeaders,
            CURLOPT_USERAGENT => 'PHP GetResponse client 0.0.2',
            CURLOPT_HTTPHEADER => ['X-Auth-Token: api-key ' . $this->api_key, 'Content-Type: application/json']
        ];

        if (!empty($this->enterprise_domain)) {
            $options[CURLOPT_HTTPHEADER][] = 'X-Domain: ' . $this->enterprise_domain;
        }

        if (!empty($this->app_id)) {
            $options[CURLOPT_HTTPHEADER][] = 'X-APP-ID: ' . $this->app_id;
        }

        if ($http_method == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $params;
        } else if ($http_method == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if (false === $response) {
            throw GetresponseApiException::create_for_invalid_curl_response(
                curl_error($curl),
                curl_getinfo($curl, CURLINFO_HTTP_CODE)
            );
        }

        curl_close($curl);

        if ($withHeaders) {
            list($headers, $response) = explode("\r\n\r\n", $response, 2);
            $this->headers = $this->prepareHeaders($headers);
        }

        return json_decode($response, true);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function setParams($params = [])
    {
        $result = [];
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $result[$key] = $value;
            }
        }
        return http_build_query($result);
    }

    /**
     * @param string $headers
     * @return array
     */
    private function prepareHeaders( $headers ) {
        $_headers = explode("\r\n", $headers);
        $headers = array();
        foreach ($_headers as $header) {
            $params = explode(':', $header, 2);
            $key = isset($params[0]) ? $params[0] : null;
            $value = isset($params[1]) ? $params[1] : null;
            $headers[trim($key)] = trim($value);
        }
        return $headers;
    }


    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}