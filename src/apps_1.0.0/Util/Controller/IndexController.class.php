<?php
namespace Util\Controller;
use Common\Controller\RestfulController;
/**
 * Description of IndexController
 *
 * @author kevin
 */
class IndexController extends RestfulController{
    private   $util;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('app_start_img');
        $this->postdata = $this->getRawBody();
        
        $this->util = D('Util' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
        
    }
    
    function app_start_img(){
        $this->return = $this->util->get_app_start_img();
        $this->responseJson();
    }
}
