<?php

namespace tp5er\EasyAliyun;

use tp5er\EasyAliyun\Support\Http;

class Dysms extends Aliyun
{

//    protected $config = [
//        //接入地址
//        'endpoint'  => 'http://dysmsapi.aliyuncs.com',
//        'regionId'  => 'cn-hangzhou',
//        'accountID' => '',
//        'signName'  => '',
//    ];

    protected function initialize()
    {
        $this->config['endpoint'] = $this->config['endpoint'] ?: 'https://sts.aliyuncs.com';
        $this->config['regionId'] = $this->config['regionId'] ?: 'cn-hangzhou';

        $this->params['RegionId'] = $this->config['regionId'];
        $this->params['Version']  = '2017-05-25';
    }

    /**
     * 发送短信
     * @param $phone
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($phone, $data)
    {
        $signName = isset($data['signName']) ? $data['signName'] : $this->config['signName'];
        unset($data['signName']);
        $params              = array_merge($this->params, [
            'Action'        => 'SendSms',
            'PhoneNumbers'  => $phone,
            'SignName'      => $signName,
            'TemplateCode'  => $data['template_code'],
            'TemplateParam' => json_encode($data, JSON_FORCE_OBJECT),
        ]);
        $params['Signature'] = $this->generateSign($params);
        return Http::get($this->config['endpoint'], $params);
    }
}