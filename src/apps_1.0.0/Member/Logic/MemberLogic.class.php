<?php

namespace Member\Logic;

/**
 * Description of MemberLogic
 *
 * @author Kevin
 */

class MemberLogic {
    private $member;
    function __construct() {
        
        $this->member = D('Member');
    }
    
    /**
     * 用户登录
     * base64_decode 后的结果应该是 111111:111111:0 这样结构的数据
     * 分号隔开，第一个是用户名，第二个是密码，第三个是appid
     */
    function login($token , $data = array()) {
        if($token) {
            $users = base64_decode($token);
            if($users) {
                $uinfo = explode(':' , $users);
                if($uinfo && is_array($uinfo)) {
                    $userinfo = $this->member->getUserInfo($uinfo[0]);
                    if($userinfo) {
                        
                        //验证密码是否正确
                        if(md5($uinfo[1].$userinfo['salt']) == $userinfo['password']) {
                            //密码验证成功
                            if($userinfo['status'] == 0) {
                                //用户锁定
                                $return['code'] = 432;
                            } else {
                                $mtoken = D('MemberToken');
//                                if($mtoken->getTokenByuid($userinfo['uid'])){      //已经登录，无法重复登录
//                                    $return['code'] = 442;
//                                }else{
                                    //生成Token
                                    $token = $mtoken->createToken($userinfo['uid']);
                                    //更改最后登录时间
                                    M('member_account')->where(array('uid' => $userinfo['uid']))->setField('last_time', NOW_TIME);
                                    //验证成功，返回用户信息
                                    $return['code'] = 200;
                                    $return['message'] = '';
                                    
                                    if($userinfo['regions'] == 0){
                                        $address = '丹阳';
                                    }else{
                                        $address = get_select(WORK_ADDRESS,$userinfo['regions']) ? get_select(WORK_ADDRESS,$userinfo['regions']) : '丹阳';
                                    }
                                    $userinfo['headimg'] = M('member_account')->where(array('uid' => $userinfo['uid']))->getField('headimg');
                                    $return['data'] = array(
                                        'id'            => $userinfo['uid'],
                                        'uid' => $userinfo['uid'],
                                        'token'         => $token,
                                        'headimg'		=> $userinfo['headimg'],
                                        'mobile'		=> $userinfo['mobile'],
                                        'nickname'		=> $userinfo['nickname'],
                                        'gender'		=> $userinfo['gender'] ? '男' : '女',
                                        'status'        => $userinfo['status'],
                                        'regions' => $userinfo['regions'],
                                        'address' => $address
                                    );
//                                }
                            }
                        } else {
                            //密码错误
                            $return['code'] = 433;
                        }
                    } else {
                        $return['code'] = 401;
                    }
                } else {
                    $return['code'] = 302;
                }
            } else {
                $return['code'] = 302;
            }
        } else {
            $return['code'] = 302;
        }
        return $return;
    }
    
    /**
     * 用户退出
     * @param $token header中的token值
     */
    function logout($token) {
        $userinfo = getUserInfo(UID , 2);
        
        //先删除缓存
        S(get_cache_key($userinfo['mobile']) , null);
        S(get_cache_key(UID , 2) , null);
        
//        $mtoken = D('MemberToken');
//        $mtoken->deleteToken($token);
        
        $return['code']    = 200;
        $return['message'] =  '';
        $return['data']    = array();
        
        return $return;
    }
    
    /**
     * 用户注册
     * @param $mobile 用户注册手机号
     * @param $username 注册用户名
     * @param $data     注册其它信息如密码等
     * @return 返回注册成功后的UID
     */
    function register($data = array()) {
        if (!$data['mobile']) {
            return -1; //用户名(手机号)为空
        }
        
        if ($this->check_exist($data['mobile'])) {
            return -2; //用户已存在
        }
        
        if(!validate_mobile($data['mobile'])){
            return -3; //手机格式不正确
        }
        
        if(empty($data['password'])){
            return -4; //密码为空
        }
        
        if(empty($data['verify_code'])){
            return -5; //验证码为空
        }
        
        //判断验证码的合法性
        $option['mobile'] = $data['mobile'];
        $option['type']   = 'register';
        $vcModel = D('Common/CommonVerifycode');
        $row = $vcModel->getVerifycodeRow($option , $data['verify_code']);
        if (!$row) {
            return -6; //验证码输入错误
        }
        if ($row['expiration'] < NOW_TIME) {
            return -7; //验证码已过期
        }
        
        //注册用户
        $salt = rand(100000, 999999); //随机码
        $gender = $data['gender'] ? $data['gender'] : 1;
        $account = array(
            'nickname' => $data['nickname'] ? emoij_to_ubb($data['nickname']) : '用户'.$salt,
            'mobile' => $data['mobile'],
            'password' => md5($data['password'] . $salt),
            'salt' => $salt,
            'headimg' => $data['headimg'] ? $data['headimg'] : getDefaultHeadimg(),
            'gender' => $gender,
            'regions' => 1,
            'dateline' => time()
        );
        $uid = M('member_account')->add($account);
        if($uid){
            $token = base64_encode($data['mobile'].':'.$data['password'].':'.$uid);
            return $this->login($token);
        }else{
            return -99;
        }
        
    }
    
    /**
     * 修改用户密码
     */
    function resetpwd($data) {
        $model = D('Member/Member');
        $userinfo = $model->getUserInfo($data['mobile']);
        if(!$userinfo){
            return 437;
        }
        
//        if(md5($data['old_password'].$userinfo['salt']) != $userinfo['password']){
//            return 438;
//        }
        
        //更新用户密码
        $salt     = mt_rand(100000, 999999);
        $new_password = md5($data['new_password'] . $salt);
        
        //先删除缓存
        S(get_cache_key($userinfo['uid'] , 2) , null);
        S(get_cache_key($userinfo['mobile'] , 1) , null);
        
        //更新数据库
        if(M('member_account')->where(array('uid' => $userinfo['uid']))->save(array('password'=>$new_password,'salt'=>$salt))){
            return 200;
        }else{
            return 435;
        }
    }
    
    /**
     * 用户资料更新
     */
    function update($data) {
        $ret_code = 445;
        $newdata = array();
        //去掉空数据
        foreach($data as $key => $value) {
            if(trim($value) || $value == 0){
                $newdata[$key] = $value;
            }
        }
        unset($newdata['action']);
        if($newdata) {
            if($newdata['headimg']) {
                if(!is_base64($newdata['headimg'])){
                    $newdata['headimg'] = urldecode($newdata['headimg']);
                }
//                if(is_base64($newdata['headimg'])){
                    //如果是修改头像，写入OSS  TODO
                    $oss = D('Common/Oss' , 'Logic');

                    $path = 'headimg';//getAvatarPath(UID);
                    $return = $oss->save($newdata['headimg'] , $path , NOW_TIME . '.jpg');
                    if($return['status'] == 200){
                        $newdata['headimg'] = $return['url'];
                        $ret_code = 200;
                    }else{
                        unset($newdata['headimg']);
                        $ret_code = 444;
                    } 
//                }else{
//                    unset($newdata['headimg']);
//                }
            }
            if($newdata['nickname']){
                $newdata['nickname'] = emoij_to_ubb($newdata['nickname']);
            }
            //删除用户缓存数据
            $mobile = M('member_account')->where(array('uid' => UID))->getField('mobile');
            S(get_cache_key(UID , 2) , null);
            S(get_cache_key($mobile , 1) , null);
            if(M('member_account')->where(array('uid' => UID))->save($newdata)){
                $ret_code = 200;
            }
            $userinfo = getUserInfo(UID , 2);
        } else {
            $ret_code = 443;  //没有更新项
        }
        
        return array('code' => $ret_code, 'data' => $userinfo);
    }
    
    /**
     * 查询用户是否存在
     * @param unknown $option 用户名或手机号
     * @param $type为手机号或ID
     * @return Array 返回用户信息
     */
    function check_exist($username) {
        if (!$username)
            return false;
        
        if(M('member_account')->where(array('mobile' => $username))->find()){
            return true;
        }
    }
    
    /**
     * 根据uid获取token
     */
    function get_token_by_uid($data){
        if(!$data['uid']){
            return array('code' => 434);
        }
        $token = M('member_token')->where(array('uid' => $data['uid']))->getField('token');
        return array('code' => 200, 'data' => array('token' => $token));
    }
}
