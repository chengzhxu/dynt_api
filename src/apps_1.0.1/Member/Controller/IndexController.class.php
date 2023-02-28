<?php

/**
 * 用户接口控制器
 * 
 * @author kevin
 * @date   
 */
namespace Member\Controller;

use Common\Controller\RestfulController;

class IndexController extends RestfulController {
    private   $member;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('login','register', 'verifycode', 'resetpwd', 'logout', 'user_info', 'update', 'get_address', 'get_token_by_uid');
        $this->postdata = $this->getRawBody();
        
        $this->member = D('Member' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
        
    }
    
    /**
     * 用户登录
     * POST
     * @param  header Authorization: Base base64_encode('用户名:密码:appid')
     * body参数  {"action":"login","device_token":"device_token"}
     */
    public function login() {
        $token = $this->getHeaders('Base');
        if($token) {
            //验证登录信息
            $this->return = $this->member->login($token , $this->postdata);
        } else {
            $this->return['code'] = 302;
        }
        
        $this->responseJson();
    }
    
    /**
     * 用户获取验证码
     * @param type 验证码的类型  type=register注册验证码 ，type=resetpwd 重置密码验证码
     * {"action":"verifycode", "type":"register"}
     */
    function verifycode(){
        //判断难类型的合法性
        $type = $this->postdata['type'];
        if (!in_array($type, C('SHORT_MSG_TYPE'))) {
            $this->return['code'] = 403;
            return $this->responseJson();
        }
        //判断手机号的合法性
        $mobile = $this->postdata['mobile'];
        if (!($mobile) || !validate_mobile($mobile)) {
            $this->return['code'] = 404;
            return $this->responseJson();
        }
    
        $code = mt_rand(100000, 999999);
        
        //判断该号码是否已经注册
        $row = D('Member')->getUserInfo($mobile);
       
        if ('register' == $type) { //注册时获取验证码
            if ($row['uid']) {
                $this->return['code'] = 402;
                return $this->responseJson();
            }
        } elseif ('resetpwd' == $type) {//找回密码时验证码
            if (!$row['uid']) {
                $this->return['code'] = 401;
                return $this->responseJson();
            }
        } elseif('binding' == $type){    //绑定第三方账号
            
        } else {
            $this->return['code'] = 301;
            return $this->responseJson();
        }
    
        //发送处理
        $uc = D('Common/Uc', 'Logic');
        $rt = $uc->send($mobile, $code);   //!$rt
        if ($rt == '000000') {   //短信发送成功,保存验证码
            $vcModel = D('Common/CommonVerifycode');
            $id = $vcModel->saveVerifycode($mobile, $code , $type);
            if ($id) {//写入验证码成功
                $this->return['code'] = 200;
            } else {
                $this->return['code'] = 413;
            }
        } else {//短信接口失败
            $this->return['code'] = 412;
        }
        return $this->responseJson();
    }
    
    /**
     * 用户注册
     * {"action":"register","username":"","password":"111111"}
     */
    function register(){
        $return = $this->member->register($this->postdata);
        if(is_array($return)) {
            $this->return = $return;
        } else {
            if (-1 == $return) {//用户名为空
                $this->return['code'] = 421;
            } elseif (-2 == $return) {//用户已经存在
                $this->return['code'] = 402;
            } elseif(-3 == $return){       //手机号码格式不正确
                $this->return['code'] = 404;
            } elseif(-4 == $return){       //密码为空
                $this->return['code'] = 431;
            } elseif(-5 == $return){       //验证码为空
                $this->return['code'] = 422;
            } elseif(-6 == $return){       //验证码输入错误
                $this->return['code'] = 423;
            } elseif(-7 == $return){       //验证码已过期
                $this->return['code'] = 424;
            }else {//写入用户数据失败
                $this->return['code'] = 399;
            }
        }
        return $this->responseJson();
    }
    
    /**
     * 判断验证码合法性
     * mobile:手机号, type:验证码类型, code:验证码
     */
    function validate_code($mobile, $type, $code){
        //判断验证码的合法性
        $option['mobile'] = $mobile;
        $option['type']   = $type;
        $vcModel = D('Common/CommonVerifycode');
        $row = $vcModel->getVerifycodeRow($option , $code);
        if (!$row) {
            return 423;
        }
        if ($row['expiration'] < NOW_TIME) {
            return 424;
        }
        return 200;
    }
    
    /**
     * 重置(修改)密码
     */
    function resetpwd(){
        $data = $this->postdata;
        
        /**
         * 判断原密码输入是否正确
         */
        if(!$data['new_password']){
            $this->return['code'] = 436;
            return $this->responseJson();
        }
        
        $row = $this->validate_code($data['mobile'], 'resetpwd', $data['code']);
        if($row != 200){
            $this->return['code'] = $row;
            return $this->responseJson();
        }
        
        $return = $this->member->resetpwd($data);
        $this->return['code'] = $return;
        return $this->responseJson();
    }
    
    /**
     * 用户更新资料
     * {"action":"update"}
     */
    function update(){
        $this->checkLogin();
        $data = $this->postdata;
        $return = $this->member->update($data);
        $this->return = $return;
        return $this->responseJson();
    }
    
    /**
     * 用户退出
     * POST 
     * @param header Authorization: Token 2323234234
     * body参数  {"action":"logout"}
     */
    public function logout() {
        
        $this->checkLogin();
        $token = $this->getHeaders('Token');
        
        if($token) {
            //验证登录信息
            $this->return = $this->member->logout($token);
        } else {
            $this->return['code'] = 302;
        }
        
        $this->responseJson();
    }
    
    /**
     * 获取用户信息
     * {"action":"user_info"}
     */
    function user_info(){
        $model = D('Member/Member');
        $this->return = $model->info('' , $this->postdata);
        
        $this->responseJson();
    }
    
    /**
     * 获取用户地区选择选项
     * ("action":"get_address")
     */
    function get_address(){
        $this->checkLogin();
        $address = getAllAddress(1);
        $rst = array('code' => 200, 'data' => $address);
        $this->return = $rst;
        $this->responseJson();
    }
    
    /**
     * 根据uid获取token
     */
    function get_token_by_uid(){
        $this->return = $this->member->get_token_by_uid($this->postdata);
        
        $this->responseJson();
    }
}