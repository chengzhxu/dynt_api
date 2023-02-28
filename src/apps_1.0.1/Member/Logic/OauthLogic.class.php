<?php

namespace Member\Logic;

/**
 * 绑定第三方账号
 *
 * @author Kevin
 */
class OauthLogic {
    function __construct() {
        
    }
    
    
    /**
     * 第三方登录
     */
    function oauth_login($data){
        if($data){
            $oauth_type = 0;
            switch ($data['source']) {
                case 'qq':             //qq登录
                    $oauth_type = 1;
                    break;
                case 'wechat':             //weinxin登录
                    $oauth_type = 2;
                    break;
                case 'sina':                //sina微博登录
                    $oauth_type = 3;
                    break;
            }
            if($oauth_type == 0){
                return array('code'=>1601,'data'=>array());
            }
            $where['type'] = $oauth_type;
            $where['openid'] = $data['usid'];
            $oauth_info = M('member_oauth')->where($where)->find();
            if($oauth_info){              //已绑定  
                $uid = $oauth_info['uid'];
                if($uid){          //直接登录
                    return $this->get_bdmember_info($uid);
                }else{          //获取用户信息失败
                    return array('code'=>1602,'data'=>array());
                }
            }else{          //未绑定
                return array('code'=>1603,'data'=>array());
            }
        }
    }
    
    
    /**
     * 绑定第三方账号
     */
    function binding_oauth($data){
        if($data){
            if(!$data['type']){
                $data['type'] = 0;
            }
            $oauth_type = 0;
            switch ($data['source']) {
                case 'qq':             //qq登录
                    $oauth_type = 1;
                    break;
                case 'wechat':             //weinxin登录
                    $oauth_type = 2;
                    break;
                case 'sina':                //sina微博登录
                    $oauth_type = 3;
                    break;
            }
            if($oauth_type == 0){
                return array('code'=>1601,'data'=>array());
            }
            $where['type'] = $oauth_type;
            $where['openid'] = $data['usid'];
            $oauth_info = M('member_oauth')->where($where)->find();
            if($oauth_info){          //该账号已绑定过
                return array('code'=>1604,'data'=>array());
            }else{          //绑定
                if(M('member_oauth')->where(array('openid' => $data['usid']))->find()){             //当前openid已存在
                    return array('code'=>1608,'data'=>array());
                }
                
                if($data['type'] == 0){          //绑定手机号
                    if(!$data['mobile']){            //未输入手机号
                        return array('code'=>1605,'data'=>array());
                    }

                    //判断验证码有效性
                    $option['mobile'] = $data['mobile'];
                    $option['type']   = 'binding';
                    $vcModel = D('Common/CommonVerifycode');
                    $row = $vcModel->getVerifycodeRow($option , $data['verify_code']);

                    if (!$row) {
                        return array('code'=>423,'data'=>array());
                    }
                    if ($row['expiration'] < NOW_TIME) {
                        return array('code'=>424,'data'=>array());
                    }
                }else if($data['type'] == 1){           //跳过绑定   直接注册
                    $data['mobile'] = $this->get_rand_mobile();
                }
                M('oauth_logs')->add(array('mobile' => $data['mobile'], 'type' => $data['type'], 'dateline' => NOW_TIME));   //记录绑定日志
                
                $b_uid = $this->exist_mobile($data['mobile']);
                if($b_uid){             //绑定手机号已存在       完善第三方登录信息
                    $m_oauth = M('member_oauth')->where(array('uid' => $b_uid, 'type' => $oauth_type))->find();
                    if($m_oauth){
                        $oauth_data = array('openid' => $data['usid'], 
                            'nickname' => $data['userName'], 'access_token' => $data['refreshToken'], 'headimg' => $data['iconURL'], 'type' => $oauth_type);
                        M('member_oauth')->where(array('id' => $m_oauth['id']))->save($oauth_data);
                        return $this->get_bdmember_info($b_uid);
                    }
                    $oauth_data = array('uid' => $b_uid, 'openid' => $data['usid'], 'nickname' => $data['userName'], 'access_token' => $data['refreshToken'], 'headimg' => $data['iconURL'], 'type' => $oauth_type);
                    if(M('member_oauth')->add($oauth_data)){       //绑定成功
                        $account = M('member_account')->where(array('uid' => $b_uid))->find();
                        if(!$account['nickname']){
                            M('member_account')->where(array('uid' => $b_uid))->save(array('nickname' => $data['userName']));
                        }
                        if(!$account['headimg']){
                            M('member_account')->where(array('uid' => $b_uid))->save(array('headimg' => $data['iconURL']));
                        }
                         
                        return $this->get_bdmember_info($b_uid);
                    }else{
                        return array('code'=>1609,'data'=>array());
                    }
                }
                
                //注册
                $data['nickname'] = $data['userName'] ? $data['userName'] : '用户' . $data['verify_code'];       //昵称
                $data['password'] = $data['password'] ? $data['password'] : '123456';
                $data['headimg'] = $data['iconURL'] ? $data['iconURL'] : getDefaultHeadimg();
                $salt = rand(100000, 999999); //随机码
                $account = array(
                    'nickname' => $data['nickname'] ? $data['nickname'] : '用户'.$salt,
                    'mobile' => $data['mobile'],
                    'password' => md5($data['password'] . $salt),
                    'salt' => $salt,
                    'headimg' => $data['headimg'],
                    'gender' => 1,
                    'regions' => 1,
                    'dateline' => time()
                );
                
                $uid = M('member_account')->add($account);
                if($uid){      //用户写入成功
                    if($data['type'] == 1){
                        $oauth_data = array('uid' => $uid, 'openid' => $data['usid'], 'nickname' => $data['userName'], 'access_token' => $data['refreshToken'], 'headimg' => $data['iconURL'], 'type' => $oauth_type);
                        M('member_oauth')->add($oauth_data);
                    }
                    $member_logic = D('Member', 'Logic');
                    $token = base64_encode($data['mobile'].':'.$data['password'].':1');
                    return $member_logic->login($token);
                }else{
                    return array('code'=>1607,'data'=>array());
                }
            }
        }
    }
    
    
    /**
     * 第三方登录绑定手机号
     */
    function oauth_binding_mobile($data, $token){
        if($data){
            if(!$data['mobile']){            //未输入手机号
                return array('code'=>1605,'data'=>array());
            }
            $uid = M('member_token')->where(array('token' => $token))->getField('uid');
            if(!$uid){
                return array('code'=>1611,'data'=>array());
            }

            //判断验证码有效性
            $option['mobile'] = $data['mobile'];
            $option['type']   = 'binding';
            $vcModel = D('Common/CommonVerifycode');
            $row = $vcModel->getVerifycodeRow($option , $data['verify_code']);

            if (!$row) {
                return array('code'=>423,'data'=>array());
            }
            if ($row['expiration'] < NOW_TIME) {
                return array('code'=>424,'data'=>array());
            }
            
            if($this->exist_mobile($data['mobile'])){
                return array('code'=>1610,'data'=>array());
            }else{
                $member_account = M('member_account')->where(array('uid' => $uid))->find();
                if($member_account){
                    $salt = rand(100000, 999999);
                    if($data['password']){
                        $pwd = md5($data['password'].$salt);
                    }else{
                        $pwd = md5('123456'.$salt);
                    }
                    M('member_account')->where(array('uid' => $uid))->save(array('password' => $pwd, 'salt' => $salt, 'mobile' => $data['mobile']));
                    return array('code'=>200,'data'=>array());
                }
            }
        }
    }
    
    
    /**
     * 手机号登录绑定第三方账号
     */
    function mobile_binding_oauth($data, $token){
        if($data){
            $oauth_type = 0;
            switch ($data['source']) {
                case 'qq':             //qq登录
                    $oauth_type = 1;
                    break;
                case 'wechat':             //weinxin登录
                    $oauth_type = 2;
                    break;
                case 'sina':                //sina微博登录
                    $oauth_type = 3;
                    break;
            }
            if($oauth_type == 0){
                return array('code'=>1601,'data'=>array());
            }
            
            $uid = M('member_token')->where(array('token' => $token))->getField('uid');
            if(!$uid){
                return array('code'=>1611,'data'=>array());
            }
            
            $m_oauth = M('member_oauth')->where(array('uid' => $uid, 'type' => $oauth_type))->find();
            if($m_oauth){       //之前已绑定该第三方账号  解除重新绑定 
                $oauth_data = array('openid' => $data['usid'], 
                    'nickname' => $data['userName'], 'access_token' => $data['refreshToken'], 'headimg' => $data['iconURL'], 'type' => $oauth_type);
                M('member_oauth')->where(array('id' => $m_oauth['id']))->save($oauth_data);
                return array('code'=>200,'data'=>array('userName' => $data['userName']));
                
            }else{
                M('member_oauth')->where(array('openid' => $data['usid']))->delete();     //解除第三方之前的绑定
                $oauth_data = array('uid' => $uid, 'openid' => $data['usid'], 'nickname' => $data['userName'], 'access_token' => $data['refreshToken'], 'headimg' => $data['iconURL'], 'type' => $oauth_type);
                if(M('member_oauth')->add($oauth_data)){
                    return array('code'=>200,'data'=>array('userName' => $data['userName']));
                }
            }
            return array('code'=>1607,'data'=>array());
        }
    }
    
    
    /**
     * 判断当前第三方账号是否被绑定过
     */
    function check_oauth($data, $token){
        if($data){
            $oauth_type = 0;
            switch ($data['source']) {
                case 'qq':             //qq登录
                    $oauth_type = 1;
                    break;
                case 'wechat':             //weinxin登录
                    $oauth_type = 2;
                    break;
                case 'sina':                //sina微博登录
                    $oauth_type = 3;
                    break;
            }
            if($oauth_type == 0){
                return array('code'=>1601,'data'=>array());
            }
            
            $where['type'] = $oauth_type;
            $where['openid'] = $data['usid'];
            $oauth_info = M('member_oauth')->where($where)->find();
            if($oauth_info){        //当前第三方账号已被绑定
                $uid = M('member_token')->where(array('token' => $token))->getField('uid');
                if($uid == $oauth_info['uid']){
                    return array('code'=>200,'data'=>array('status' => 1, 'type' => 1));
                }else{
                    return array('code'=>200,'data'=>array('status' => 1, 'type' => 0));
                }
            }else{
                return array('code'=>200,'data'=>array('status' => 0, 'type' => 0));
            }
        }
    }
    
    
    
    /**
     * 判断当前绑定手机号是否存在
     */
    function exist_mobile($mobile){
        if($mobile){
            return M('member_account')->where(array('mobile' => $mobile))->getField('uid');
        }
    }
    
    
    
    /**
     * 获取绑定用户信息
     */
    private function get_bdmember_info($uid = 0){
        $member_model = D('Member');
        $userinfo = $member_model->getUserInfo($uid, 2);
        if($userinfo['status'] == 0) {
            //用户锁定
            $return['code'] = 432;
        } else {
            //生成Token
            $mtoken = D('MemberToken');
            $token = $mtoken->createToken($userinfo['uid']);
            //登录时间
            M('member_account')->where( array( 'uid'=>$userinfo['uid']) )->setField('last_time',NOW_TIME);
            //验证成功，返回用户信息
            $return['code'] = 200;
            $return['message'] = '';

            if($userinfo['regions'] == 0){
                $address = '丹阳';
            }else{
                $address = get_select(WORK_ADDRESS,$userinfo['regions']) ? get_select(WORK_ADDRESS,$userinfo['regions']) : '丹阳';
            }
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
        }
            
        return $return;
    }
    
    
    /**
     * 生成随机手机号(144开头)
     */
    function get_rand_mobile(){
        $max_uid_sql = "SELECT max( uid ) AS max_uid FROM dy_member_account";
        $max_uid = M()->query($max_uid_sql)[0]['max_uid'];
        
        $mobile = '144' . $max_uid;
        $count = 11 - strlen($mobile);
        for($i = 0; $i < $count; $i++){
            $mobile .= rand(0, 9);
        }
        if(M('member_account')->where(array('mobile' => $mobile))->find()){
            $mobile = $this->get_rand_mobile();
        }
        return $mobile;
    }
}
