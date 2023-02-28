<?php

namespace Content\Controller;
use Common\Controller\RestfulController;

/**
 * 收藏相关
 *
 * @author kevin
 */
class FavoriteController extends RestfulController{
    private   $favorite;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('favorite', 'my_favorite');
        $this->postdata = $this->getRawBody();
        
        $this->favorite = D('Favorite' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
    }
    
    /**
     * 收藏 or 取消收藏
     */
    function favorite(){
        $this->checkLogin();
        
        $this->return = $this->favorite->favorite($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取我的收藏
     * {"action":"my_favorite"}
     */
    function my_favorite(){
        $this->checkLogin();
        
        $this->return = $this->favorite->my_favorite($this->postdata);
        return $this->responseJson();
    }
}
