<?php

namespace Common\Logic;

/**
 * Sign处理类
 *
 * @author Kevin
 */
class UtilLogic {

    private $key;
    
    function __construct() {
        $this->key = '96D9QQRW65A21UQXXXMV6MBL4MAF78T3VH19NFKW86BWYCA7ASC8AAZV5BNQQRJ1';
    }
    
    /**
     * 验证sign是否合法
     * sign算法  md5(版本+客户端类型+客户端时间+KEY)
     * @param unknown $data
     */
    function validateSign($sign) {
        
        $realSign = md5(VERSION . CLIENT . CLIENTTIME . $this->key);
        
        if(S($sign , '') == 1)  //获取当前的 sign是否之前有访问过，如果有的话直接返回错误
            return -3;

        if($sign == $realSign) {
            //验证成功
            if(NOW_TIME*1000 > CLIENTTIME + 600000) {
                return -2;
            } else {
                S($sign , 1 , 602);  //cache保存10分钟
                return 1;
            }
        } else {
            //sign验证失败
            return -1;
        }
    }

    /**
     * 用户的访问记录
     */
    function userVisitLog($action = '') {
    
        $log = array(
            'client' => CLIENT,
            'version' => VERSION ? VERSION : '0.0.1',
            'appid'   => APPID,
            'uid'     => UID > 0 ? UID :0,
            'url'     => MODULE_NAME . '/' . ACTION_NAME,
            'action'  => $action,
            'addtime' => NOW_TIME
        );
        M('common_visitlog')->add($log);
    }
}
