<?php

namespace tp5er\EasyAliyun;

use tp5er\EasyAliyun\Support\Http;

class Sts extends Aliyun
{

    protected $config = [
        //接入地址
        'endpoint'  => 'https://sts.aliyuncs.com',
        'accountID' => '',
        'roleName'  => '',
    ];

    protected function initialize()
    {
        $this->config['endpoint'] = $this->config['endpoint'] ?: 'https://sts.aliyuncs.com';
    }

    /**
     * RoleArn 角色账号生成
     * @return string
     */
    protected function buildRoleArn()
    {
        return 'acs:ram::' . $this->config['accountID'] . ':role/' . strtolower($this->config['roleName']);
    }

    /**
     * 调用AssumeRole接口获取一个扮演该角色的临时身份，此处RAM用户扮演的是受信实体为阿里云账号类型的RAM角色。
     * https://help.aliyun.com/document_detail/28763.html
     * @param string $roleSessionName 用户自定义参数。此参数用来区分不同的令牌，可用于用户级别的访问审计
     * @param int $durationSeconds 过期时间，单位为秒。
     * @param string $policy
     * @return string
     */
    public function assumeRole($roleSessionName = 'alice', $durationSeconds = 3600, $policy = '')
    {
        $params = array_merge($this->params, [
            'Action'          => 'AssumeRole',
            'RoleArn'         => $this->buildRoleArn(),
            'RoleSessionName' => $roleSessionName,
            'DurationSeconds' => $durationSeconds
        ]);
        if ($policy) {
            $params['Policy'] = $policy;
        }
        $params['Signature'] = $this->generateSign($params);
        return Http::get($this->config['endpoint'], $params);
    }

    /**
     * 调用GetCallerIdentity接口获取当前调用者的身份信息。
     * https://help.aliyun.com/document_detail/43767.html
     * @return string
     */
    public function getCallerIdentity()
    {
        $params              = array_merge($this->params, [
            'Action' => 'GetCallerIdentity',
        ]);
        $params['Signature'] = $this->generateSign($params);
        return Http::get($this->config['endpoint'], $params);
    }
}