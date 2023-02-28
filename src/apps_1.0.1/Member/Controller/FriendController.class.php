<?php
namespace Member\Controller;
use Common\Controller\RestfulController;
/**
 * 用户相关接口
 *
 * @author Kevin
 */
class FriendController extends RestfulController{
    private   $friend;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('follow', 'my_follow', 'my_fans', 'defriend', 'my_blacklist', 'remove_blacklist');
        $this->postdata = $this->getRawBody();
        
        $this->friend = D('Friend' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
    }
    
    /**
     * 关注用户
     * @param fid 被关注者的uid
     * {"action":"follow","fid":"1"}
     */
    function follow(){
        $this->checkLogin();
        
        $this->return = $this->friend->follow($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取我的关注
     */
    function my_follow(){
        
        $this->return = $this->friend->my_follow($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取我的粉丝
     */
    function my_fans(){
        
        $this->return = $this->friend->my_fans($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 拉黑/屏蔽某人
     * {"action":"defriend","bid":"1","type":"1"}
     */
    function defriend(){
        $this->checkLogin();
        $this->return = $this->friend->defriend($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 我的黑名单
     * {"action":"my_blacklist","page":"1"}
     */
    function my_blacklist(){
        $this->checkLogin();
        $this->return = $this->friend->my_blacklist($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 移除黑名单
     * {"action":"remove_blacklist","bid":"1"}
     */
    function remove_blacklist(){
        $this->checkLogin();
        $this->return = $this->friend->remove_blacklist($this->postdata);
        return $this->responseJson();
    }
}
