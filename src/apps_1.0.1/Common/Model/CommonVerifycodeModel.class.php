<?php

namespace Common\Model;

use Think\Model;

/**
 * 验证码
 *
 * @author Kevin
 */
class CommonVerifycodeModel extends Model {

    /**
     * 保存验证码
     * @param type $mobile 手机号
     * @param type $code   手机验证码
     * @param $type        发送验证码的类型
     * 5分钟有效期
     * @return id
     */
    function saveVerifycode($mobile, $code , $type) {
        $data = array(
            'mobile'     => $mobile,
            'code'       => $code,
            'type'       => $type,
            'dateline'   => NOW_TIME,
            'expiration' => NOW_TIME+1800 ,
        );
        return M('common_verifycode')->add($data);
    }

    /**
     * 查找用户最后一次获取的验证码，然后再和code对比是否正确
     * @param unknown $option 查询条件，mobile,type手机号和验证码类型
     * @param unknown $code   用户输入的手机号
     * @return boolean
     */
    function getVerifycodeRow($option = array() , $code) {
        if (!$option)
            return false;
        $codeinfo = M('common_verifycode')->where($option)->order('id desc')->find();
        
        if($codeinfo) {
            if($codeinfo['code'] == $code)
                return $codeinfo;
            else 
                return false;
        } else
            return false;
    }

}
