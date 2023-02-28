<?php
namespace Welfare\Controller;
use Common\Controller\RestfulController;

/**
 * 福利社
 * @author Kevin
 *
 */
class IndexController extends RestfulController {
    
    protected $postdata;  //body内信息
    protected $welfare;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
    
        parent::_initialize();
        $this->postdata = I('post.');
        $this->welfare = D('Welfare');
    
    }
    
    /**
     * 福利社首页
     *  post参数  {"page":1}
     */
    function index() {
        $this->return = $this->welfare->getIndex($this->postdata);
        
        $this->responseJson();
    }

    /**
     * 福利详情
     * post参数{"id":1}
     */
    function detail() {
        $return = $this->welfare->getDetail($this->postdata);
        
        if(-1 == $return) {
            $this->return['code'] = 302;
        } else {
            $this->return = $return;
        }
        $this->responseJson();
        
    }
    
}