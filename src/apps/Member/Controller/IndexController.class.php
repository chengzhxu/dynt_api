<?php

/**
 * 用户账号相关接口控制器
 * 
 * @author Kevin
 * @date   
 */
namespace Member\Controller;

use Common\Controller\RestfulController;

class IndexController extends RestfulController {
    
    protected $postdata;  //post内信息  
    private   $member;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        parent::_initialize();
        $this->postdata = I('post.');
        $this->member = D('Member' , 'Logic');  //初始化用户逻辑处理类
        
    }
    
    /**
     * 用户登录
     * POST
     * post参数  {"username":"aaa","password":"123456"}
     */
    public function login() {
        $username = $this->postdata['username'];
		$password = $this->postdata['password'];
        if($username && $password) {
            //验证登录信息
            $this->return = $this->member->login($username.':'.$password.':1');
        } else {
            $this->return['code'] = 302;
        }
        
        $this->responseJson();
    }
    
    /**
     * 用户注册
     * POST
     * post参数{"mobile":"15021795274","verify_code":"712434"}
     */
    public function register() {
        
        $mobile = $this->postdata['mobile']; //手机号即为登陆用户名
        //验证输入合法性
        $this->_register_verify($mobile);
        
        $member_logic = D('Member', 'Logic');
        $data = $this->postdata;
        $data['source'] = 1;
		$data['password'] = '000000';
        $uid = $member_logic->register($mobile, $mobile, $data);
        if ($uid < 1) {
            if (-1 == $uid) {//用户名或手机号为空
                $this->return['code'] = 421;
            } elseif (-2 == $uid) {//用户已经存在
                $this->return['code'] = 402;
            } else {//写入用户数据失败
                $this->return['code'] = 399;
            }
        } else {
            $this->return['code'] = 200;
        }
        return $this->responseJson();
        
    }
    
    /**
     * 用户注册验证
     * @param type $mobile
     * @return type
     */
    private function _register_verify($mobile) {
        //验证有效手机号码
        if (!validate_mobile($mobile)) {
            $this->return['code'] = 404;
            return $this->responseJson();
        }
        //验证码不能为空
        if (empty($this->postdata['verify_code'])) {
            $this->return['code'] = 422;
            return $this->responseJson();
        }
    
        //判断验证码有效性
        $option['mobile'] = $mobile;
        $option['type']   = 'register';
        $vcModel = D('Common/CommonVerifycode');
        $row = $vcModel->getVerifycodeRow($option , $this->postdata['verify_code']);
        
        if (!$row) {
            $this->return['code'] = 423;
            return $this->responseJson();
        }
        if ($row['expiration'] < NOW_TIME) {
            $this->return['code'] = 424;
            return $this->responseJson();
        }
    }
    
    /**
     * 取验证码，目前提供两种类型的验证码获取：注册、找回密码
     * @param type 验证码的类型  type=register注册验证码 ，type=resetpwd 重置密码验证码，type=bindmobile绑定手机号
     * body内参数 {"action":"verifycode","type":"register","mobile":"152025"}
     */
    function verifycode() {
        $sms_messages = C('SMS_MESSAGES');
        //判断难类型的合法性
        $type = $this->postdata['type'];
        if (!in_array($type, array('register', 'resetpwd' , 'bindmobile'))) {
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
            
            $msg = sprintf($sms_messages['register'], $code);
        } elseif ('resetpwd' == $type) {//找回密码时验证码
            if (!$row['profile']['uid']) {
                $this->return['code'] = 401;
                return $this->responseJson();
            }
            $msg = sprintf($sms_messages['resetpwd'], $code);
        } elseif ('bindmobile' == $type) {//绑定手机号时验证码
            
            $msg = sprintf($sms_messages['bindmobile'], $code);
        } else {
            $this->return['code'] = 301;
            return $this->responseJson();
        }
    
        //发送处理
        $sms = D('Common/Sms', 'Logic');
        $rt = $sms->send($mobile, $msg);
        if (!$rt) {//短信类操作失败
            $this->return['code'] = 411;
        } elseif (0 == $rt['code']) { //短信发送成功,保存验证码
            $vcModel = D('Common/CommonVerifycode');
            $id = $vcModel->saveVerifycode($mobile, $code , $type);
            if ($id) {//写入验证码成功
                $this->return['code'] = 200;
            } else {
                $this->return['code'] = 413;
            }
        } else {//短信接口失败
            $this->return['code'] = 412;
            $this->return['data'] = $rt;
        }
        return $this->responseJson();
    }
    
    /**
     * 核实验证码正确性
     * @param $return=true直接输出，
     * {"action":"validatecode","type":"register","mobile":"15058524","code":"712434"}
     * @return type
     */
    function validatecode($return = false) {
        $mobile = $this->postdata['mobile'];
        $code   = $this->postdata['code'];
        $type   = $this->postdata['type'];
        
        if (!$mobile) {
            $this->return['code'] = 421;
            return $this->responseJson();
        }
    
        if (!$code) {
            $this->return['code'] = 422;
            return $this->responseJson();
        }
    
        $vcModel = D('Common/CommonVerifycode');
        
        $option['mobile'] = $mobile;
        $option['type']   = $type;
        $row = $vcModel->getVerifycodeRow($option , $code);
        if (!$row) {
            $this->return['code'] = 423;
            return $this->responseJson();
        }
        
        if ($row['expiration'] < NOW_TIME) {
            $this->return['code'] = 424;
            return $this->responseJson();
        }
        
        //返回还是输出
        if(!$return) {
            $this->return['code'] = 200;
            return $this->responseJson();
        } else {
            return true;
        }
        
    }
    
    /**
     * 用户收货地址
     * POST
     * post参数 {"username":"Kevin","mobile":"1505215273","province":"上海","city":"上海市","area":"普陀区","street":"金沙江路"}
     */
    function address() {
        
        $this->checkLogin();
        $return = D('MemberAddress')->updateAddress($this->postdata);
		$this->return = $return;
        return $this->responseJson();
    }
    
    /***
     * 获取用户个人的收货地址
     */
    function getaddress() {
        
        $this->checkLogin();
        
        $return = D('MemberAddress')->getAddress($this->postdata);
        if($return){
            $this->return['data'] = $return;
		}
        else{
            $this->return['data'] = array();
		}
        
        $this->responseJson();
    }

	/***
     * 设置默认收货地址
     * POST
     * post参数{"id":10}
     */
    function defaultaddress() {
        
        $this->checkLogin();
        
        $return = D('MemberAddress')->setDefaultAddress($this->postdata);
        if($return)
            $this->return['code'] = 200;
        else 
            $this->return['code'] = 399;
        
        $this->responseJson();
    }

	/***
     * 获取一条收货地址
     * POST
     * post参数{"id":10}
     */
    function getoneaddress() {
        $this->checkLogin();
        $this->return = D('MemberAddress')->getoneaddress($this->postdata);
        $this->responseJson();
    }
    
    /**
     * 获取用户积分记录
     * 
     * {"action":"creditlog","page":1}
     */
    function creditlog() {
        
        $this->checkLogin();
        
        $credit = D('Credit' , 'Logic');
        $this->return = $credit->get_credit_list($this->postdata);
        
        $this->return['code'] = 200;
        return $this->responseJson();
        
    }
    
    
    
    /**
     * 手机号是否存在
     * 
     * {"action":"mobileexists","mobile":"125","type":"bindmobile"}
     */
    function mobileexists() {
        
        if(!validate_mobile($this->postdata['mobile']))
            $this->return['code'] = 404;
        else {
            if($this->postdata['type'] && $this->postdata['type'] == 'bindmobile') {
                $this->checkLogin();
                $return = $this->member->mobile_bind($this->postdata['mobile']);
                
                if($return)
                    $this->return['code'] = 200;  //不存在
                else
                    $this->return['code'] = 443;  //己存在
                
            } else {
                $return = $this->member->check_exist($this->postdata['mobile']);
                
                if($return)
                    $this->return['code'] = 402;  //己存在
                else
                    $this->return['code'] = 200;  //不存在
            }
        }
        
        $this->responseJson();
    }

	


	/**
     * 删除收货地址
     */
	function deladdress(){
	    $this->checkLogin();
		$return = D('MemberAddress')->delAddress($this->postdata);
		$this->responseJson();
	}

    
}