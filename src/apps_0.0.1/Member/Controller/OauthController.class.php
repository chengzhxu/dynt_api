<?php

namespace Member\Controller;
use Common\Controller\RestfulController;

/**
 * 绑定第三方账号
 *
 * @author Kevin
 */
class OauthController extends RestfulController{
    private $oauth;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('oauth_login', 'binding_oauth', 'oauth_binding_mobile', 'mobile_binding_oauth', 'check_oauth');
        $this->postdata = $this->getRawBody();
        
        $this->oauth = D('Oauth' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
    }
    
    
    /**
     * 第三方登录
     */
    function oauth_login(){
        $this->return = $this->oauth->oauth_login($this->postdata);
        return $this->responseJson();
    }
    
    
    /**
     * 绑定第三方账号
     */
    function binding_oauth(){
        $this->return = $this->oauth->binding_oauth($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 第三方登录绑定手机号
     */
    function oauth_binding_mobile(){
        $token = $this->getHeaders('Token');
        $this->return = $this->oauth->oauth_binding_mobile($this->postdata, $token);
        return $this->responseJson();
    }
    
    /**
     * 手机号登录绑定第三方账号
     */
    function mobile_binding_oauth(){
        $token = $this->getHeaders('Token');
        $this->return = $this->oauth->mobile_binding_oauth($this->postdata, $token);
        return $this->responseJson();
    }
    
    
    /**
     * 判断当前第三方账号是否被绑定过
     */
    function check_oauth(){
        $token = $this->getHeaders('Token');
        $this->return = $this->oauth->check_oauth($this->postdata, $token);
        return $this->responseJson();
    }
}
