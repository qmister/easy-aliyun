<?php

namespace tp5er\EasyAliyun;

abstract class Aliyun
{

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $params = [
        'accessKeyId'     => '',
        'accessKeySecret' => '',
    ];


    public function __construct($config)
    {
        $this->config                     = $config;
        $this->params['Format']           = $this->config['format'] ?: 'json';
        $this->params['Version']          = '2015-04-01';
        $this->params['SignatureMethod']  = 'HMAC-SHA1';
        $this->params['SignatureVersion'] = '1.0';
        $this->params['Timestamp']        = gmdate('Y-m-d\TH:i:s\Z');
        $this->params['AccessKeyId']      = $this->config['accessKeyId'];
        $this->params['SignatureNonce']   = $this->config['signatureNonce'] ?: uniqid();
        $this->initialize();
    }

    /**
     * @return mixed
     */
    abstract protected function initialize();

    /**
     * 阿里云签名生成
     * @param $params
     * @return string
     */
    protected function generateSign($params)
    {
        ksort($params);
        $accessKeySecret = $this->config['accessKeySecret'];
        $stringToSign    = 'GET&%2F&' . urlencode(http_build_query($params, null, '&', PHP_QUERY_RFC3986));
        return base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
    }
}