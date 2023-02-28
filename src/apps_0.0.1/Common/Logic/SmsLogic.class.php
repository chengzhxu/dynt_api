<?php

namespace Common\Logic;

/**
 * 短信发送类
 *
 * @author Kevin
 */
class SmsLogic {

    private $config;

    /**
     * 
     */
    function __construct() {
        $this->config = array(
            'uid' => '801481d',
            'auth' => 'Care' . 'pqq105999d',
            'gateway' => 'http://210.5.158.31:9011/hy/d',
        );
    }

    /**
     * 
     * @param type $mobile
     * @param type $message
     */
    function send($mobile, $message) {
        $rt = array('code' => 0, 'error' => '');
        $curl = new \Org\Net\Curl();
        if (!$curl->create()) {
            return false;
        }
        $send_headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        );
        $fields = array(
            'uid' => $this->config['uid'],
            'auth' => strtolower(md5($this->config['auth'])),
            'mobile' => is_array($mobile) ? implode(',', $mobile) : $mobile,
            'msg' => urlEncode($message),
            'encode' => 'utf-8',
            'expid' => 0,
        );
//         \Think\Log::record(print_r($fields,1), 'INFO');
        $url = $this->config['gateway'];
        $rtStr = $curl->post($url, $fields, false, $send_headers);
        list($code, $msgid) = explode(',', $rtStr);
        $rt['code'] = ('0' == $code) ? '0' : '-99';
        $rt['msgid'] = $msgid ? $msgid : 0;
        $rt['message'] = $this->_code($rt['code']);
//         \Think\Log::record(print_r($rt,1), 'INFO');
        return $rt;
    }

    private function _code($code) {
        //短信接口返回代码
        $codes = array(
            '0' => '操作成功',
            '-99' => '接口失败',
            '-1' => '签权失败',
            '-2' => '未检索到被叫号码',
            '-3' => '被叫号码过多',
            '-4' => '内容未签名',
            '-5' => '内容过长',
            '-6' => '余额不足',
            '-7' => '暂停发送',
            '-8' => '保留',
            '-9' => '定时发送时间格式错误',
            '-9' => '下发内容为空',
            '-9' => '账户无效',
            '-9' => 'Ip地址非法',
            '-9' => '操作频率快',
            '-9' => '操作失败',
            '-9' => '拓展码无效(1-999)',
        );
        $msg = array_key_exists($code, $codes) ? $codes[$code] : '未知错误！';
        return $msg;
    }

}
