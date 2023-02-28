<?php

namespace Topic\Controller;
use Common\Controller\RestfulController;

/**
 * 话题接口相关
 *
 * @author Kevin
 */
class IndexController extends RestfulController{
    private   $topic;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('add_topic', 'topic_list', 'topic_detail', 'del_topic', 'get_topic_column');
        $this->postdata = $this->getRawBody();
        
        $this->topic = D('Topic' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
    }
    
    /**
     * 发布话题
     * {"action":"add_topic", "data":data}
     */
    function add_topic(){
        print_r('asss');exit;
        $this->return = array('code'=> 999, 'data' => $_FILES, 'xxx'=> 'thumbssssssssss');//$this->topic->add_topic($this->postdata);
        //$this->checkLogin();
        //$this->postdata['thumbs'] = $_FILES;
        
        return $this->responseJson();
    }
    
    /**
     * 获取话题列表
     * {"action":"topic_list","type":0(0:最新;1:关注),"uid":1}
     */
    function topic_list(){
        $this->return = $this->topic->get_topic_list($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 话题详情
     * {"action":"topic_detail","topic_id":1}
     */
    function topic_detail(){
        $this->return = $this->topic->get_topic_detail($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 删除话题
     * {"action":"del_topic","topic_id":1}
     */
    function del_topic(){
        $this->checkLogin();
        $this->return = $this->topic->del_topic($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取话题栏目列表
     * {"action":"get_topic_column"}
     */
    function get_topic_column(){
        $this->return = $this->topic->get_topic_column_list($this->postdata);
        return $this->responseJson();
    }
}
