<?php

namespace Content\Controller;
use Common\Controller\RestfulController;

/**
 * 点赞相关
 *
 * @author Kevin
 */
class PraiseController extends RestfulController{
    private   $praise;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('praise', 'praise_count');
        $this->postdata = $this->getRawBody();
        
        $this->praise = D('Praise' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
    }
    
    /**
     * 点赞 or 取消点赞
     * {"action":"praise","objtype":1,"objid":1,"uid":1}
     */
    function praise(){
        $this->checkLogin();
        
        $this->return = $this->praise->praise($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取对象点赞数量
     * {"action":"praise_count","objtype":1,"objid":1}
     */
    function praise_count(){
//        $this->return = $this->praise->get_praise_count($this->postdata);
//        return $this->responseJson();
    }
}
