<?php

namespace Content\Controller;
use Common\Controller\RestfulController;

/**
 * 评论相关
 *
 * @author Kevin
 */
class CommentController extends RestfulController{
    private   $comment;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('comment','comment_list','del_comment');
        $this->postdata = $this->getRawBody();
        
        $this->comment = D('Comment' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
    }
    
    /**
     * 评论
     * {"action":"comment","content":"","objtype":1,"objid":1,"parent_id":0,"uid":1}
     */
    function comment(){
        $this->checkLogin();
        
        $this->return = $this->comment->comment($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取评论列表
     */
    function comment_list(){
        $data = $this->postdata;
        $data['page'] = $data['page'] ? $data['page'] : 1;
        $this->return = $this->comment->get_comment_list($data['objtype'], $data['objid'], $data['page']);
        return $this->responseJson();
    }
    
    /**
     * 删除评论
     * {"action":"del_comment"}
     */
    function del_comment(){
        $this->checkLogin();
        
        $this->return = $this->comment->del_comment($this->postdata);
        return $this->responseJson();
    }
}
