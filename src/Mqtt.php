<?php

namespace tp5er\EasyAliyun;


use tp5er\EasyAliyun\Support\Http;

class Mqtt extends Aliyun
{
//    protected $config = [
//        //接入地址
//        'endpoint'       => 'https://sts.aliyuncs.com',
//        'instanceId'     => '',
//        'groupId'        => '',
//        'topic'          => '',
//        'deviceId'       => '',
//        'clientId'       => '',
//        'useTLS'         => false,
//        'connectTimeout' => 5,
//        'socketTimeout'  => 5,
//        'resendTimeout'  => 5,
//    ];
    /**
     * @var \PhpMqtt\Client\MqttClient
     */
    protected $mqtt;

    protected function initialize()
    {
        $this->config['endpoint']         = $this->config['endpoint'] ?: 'onsmqtt.mq-internet-access.aliyuncs.com';
        $this->config['regionId']         = $this->config['regionId'] ?: 'cn-hangzhou';
        $this->config['port']             = $this->config['port'] ?: 1883;
        $this->config['sslPort']          = $this->config['sslPort'] ?: 8883;
        $this->config['webSocketPort']    = $this->config['webSocketPort'] ?: 80;
        $this->config['webSocketSslPort'] = $this->config['webSocketSslPort'] ?: 443;
        $this->config['flashPort']        = $this->config['flashPort'] ?: 843;
        $this->config['useTLS']           = $this->config['useTLS'] ?: false;
        $this->config['socketTimeout']    = $this->config['socketTimeout'] ?: 5;
        $this->config['resendTimeout']    = $this->config['resendTimeout'] ?: 5;


        $this->params['Version'] = '2020-04-20';
    }

    /**
     * @return string
     */
    protected function password()
    {
        $hash = hash_hmac('sha1', $this->config['clientId'], $this->config['accessKeySecret'], true);
        return base64_encode($hash);
    }


    /**
     * @return string
     */
    protected function username()
    {
        return 'Signature|' . $this->config['acessKeyID'] . '|' . $this->config['instanceId'];
    }

    /**
     * @return int
     */
    protected function webSocketPort()
    {
        return $this->config['useTLS'] ? $this->config['webSocketSslPort'] : $this->config['webSocketPort'];
    }

    /**
     * @return int
     */
    protected function port()
    {
        return $this->config['useTLS'] ? $this->config['sslPort'] : $this->config['port'];
    }

    /**
     * @return \PhpMqtt\Client\MqttClient
     * @throws \PhpMqtt\Client\Exceptions\ConfigurationInvalidException
     * @throws \PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException
     * @throws \PhpMqtt\Client\Exceptions\ProtocolNotSupportedException
     */
    public function mqttClient()
    {
        $this->mqtt         = new \PhpMqtt\Client\MqttClient($this->config['endpoint'], $this->port(), $this->config['clientId']);
        $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings())
            ->setUsername($this->username())
            ->setPassword($this->password())
            ->setUseTls($this->config['useTLS'])
            ->setSocketTimeout($this->config['socketTimeout'])
            ->setResendTimeout($this->config['resendTimeout'])
            ->setConnectTimeout($this->config['connectTimeout']);
        $this->mqtt->connect($connectionSettings, true);
        return $this->mqtt;
    }

    /**
     * @param $deviceId
     * @return string
     */
    public function clientId($deviceId)
    {
        return $this->config['groupId'] . '@@@' . $deviceId;
    }

    /**
     * 获取客户端
     * @param $deviceId
     * @return array
     */
    public function getClientInfo($deviceId)
    {
        return [
            'endpoint'      => $this->config['endpoint'],
            'useTLS'        => $this->config['useTLS'],
            'port'          => $this->port(),
            'webSocketPort' => $this->webSocketPort(),
            'username'      => $this->username(),
            'password'      => $this->password(),
            'clientId'      => $this->clientId($deviceId),
        ];
    }

    /**
     * @param $topic
     * @param $message
     * @param $qualityOfService
     * @param $retain
     * @return mixed
     * @throws \PhpMqtt\Client\Exceptions\ConfigurationInvalidException
     * @throws \PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException
     * @throws \PhpMqtt\Client\Exceptions\DataTransferException
     * @throws \PhpMqtt\Client\Exceptions\ProtocolNotSupportedException
     * @throws \PhpMqtt\Client\Exceptions\RepositoryException
     */
    public function publish($topic, $message, $qualityOfService = 0, $retain = false)
    {
        $this->mqttClient()->publish($topic, $message, $qualityOfService, $retain);
        return $this->config['deviceId'];
    }

    /**
     * @param $toDeviceId
     * @param $message
     * @return mixed
     * @throws \PhpMqtt\Client\Exceptions\ConfigurationInvalidException
     * @throws \PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException
     * @throws \PhpMqtt\Client\Exceptions\DataTransferException
     * @throws \PhpMqtt\Client\Exceptions\ProtocolNotSupportedException
     * @throws \PhpMqtt\Client\Exceptions\RepositoryException
     */
    public function p2pPublish($toDeviceId, $message)
    {
        $p2p_topic = $this->config['topic'] . '/p2p/' . $this->clientId($toDeviceId);
        return $this->publish($p2p_topic, $message);
    }

    /**
     * @param $deviceId
     * @return string
     */
    public function querySessionByDeviceId($deviceId)
    {
        $params              = array_merge($this->params, [
            'Action'     => 'QuerySessionByClientId',
            'ClientId'   => $this->clientId($deviceId),
            'InstanceId' => $this->config['instanceId'],
            'RegionId'   => $this->config['regionId'],
        ]);
        $params['Signature'] = $this->generateSign($params);
        return Http::get($this->config['endpoint'], $params);
    }

    /**
     * @throws \PhpMqtt\Client\Exceptions\DataTransferException
     */
    public function __destruct()
    {
        $this->mqtt->disconnect();
    }
}