<?php

namespace tp5er\EasyAliyun\Support;

use GuzzleHttp\Client;


class Http
{
    /**
     * @var \string[][]
     */
    public static $config = [
        'headers' => [
            'User-Agent' => 'copyright form php-cli(https://github.com/php-cli)',
        ]
    ];

    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @return string
     */
    public static function get($url, array $query = [], array $headers = [])
    {
        $options = [
            'headers' => $headers,
            'query'   => $query,
        ];

        return static::httpRequest('GET', $url, $options);
    }

    /**
     * @param $url
     * @param array $params
     * @param array $headers
     * @return string
     */
    public static function post($url, array $params = [], array $headers = [])
    {
        $options = [
            'headers'     => $headers,
            'form_params' => $params,
        ];
        return static::httpRequest('POST', $url, $options);
    }

    /**
     * @param $url
     * @param array $params
     * @param array $headers
     * @return string
     */
    public static function postJson($url, array $params = [], array $headers = [])
    {
        $options = [
            'headers' => $headers,
            'json'    => $params,
        ];
        return static::httpRequest('POST', $url, $options);
    }

    /**
     * @param $method
     * @param $url
     * @param $options
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function httpRequest($method, $url, $options)
    {
        $resp = static::httpClient()->request($method, $url, $options);
        return $resp->getBody()->getContents();
    }

    /**
     * @return Client
     */
    public static function httpClient()
    {
        return new Client(static::$config);
    }
}