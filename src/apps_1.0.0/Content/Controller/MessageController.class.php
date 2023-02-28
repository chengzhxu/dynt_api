<?php

namespace Content\Controller;
use Common\Controller\RestfulController;

/**
 * 消息管理
 *
 * @author Kevin
 */
class MessageController extends RestfulController{
    private $message;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('add_message', 'message_list', 'read_message', 'del_message', 'no_read_message', 'unread_comment');
        $this->postdata = $this->getRawBody();
        
        $this->message = D('Message' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
    }
    
    /**
     * 新增消息
     * {"action":"add_message","data":消息体}
     */
    function add_message(){
        $this->checkLogin();
        
        $this->return = $this->message->add_message($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取消息列表
     * {"action":"message_list","type":-1,token}
     */
    function message_list(){
        $this->checkLogin();
        
        $this->return = $this->message->get_message_list($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 设为已读消息
     * {"action":"read_message","id":1}
     */
    function read_message(){
        $this->checkLogin();
        
        $this->return = $this->message->read_message($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 删除消息
     * {"action":"del_message","id":1}
     */
    function del_message(){
        $this->checkLogin();
        
        $this->return = $this->message->del_message($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 未读消息数量Socket
     */
    function no_read_message(){
        $this->checkLogin();
        
        $this->return = $this->message->getNotReadMsg($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 未读评论列表
     */
    function unread_comment(){
        $this->checkLogin();
        
        $this->return = $this->message->get_unread_comment($this->postdata);
        return $this->responseJson();
    }
}
