<?php

namespace Common\Logic;

/**
 * 短信发送
 *
 * @author Kevin
 */
class UcLogic {
    private $config;
    
    /**
     * 
     */
    function __construct() {
        $this->config = array(
            'accountsid' => '64aa3008fffeaaf52f047d58194505ef',
            'token' => '708641063f52c08885435f84c335d3cd',
            'appid' => '28c80da5d1304f20bca4ea34072a74db',
            'templateid' => 44689
        );
    }
    
    function send($mobile, $msg_code){
        //$rt = array('code' => 0, 'error' => '');
        $ucpass = new \Org\Net\Ucpaas($this->config);
        $result = $ucpass->templateSMS($this->config['appid'], $mobile, $this->config['templateid'], $msg_code);
        $rst = (array)json_decode($result);
        $resp = (array)$rst['resp'];
        $code = $resp['respCode'];
        return $code;
    }
}
