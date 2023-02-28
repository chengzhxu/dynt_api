<?php

namespace Work\Controller;
use Common\Controller\RestfulController;

/**
 * 工作模块相关
 *
 * @author kevin
 */
class IndexController extends RestfulController{
    private   $recruit;
    private   $job;
    
    /**
     * 构造函数
     * 初始化数据
     */
    public function _initialize() {
        
        parent::_initialize();
        
        //定义action
        $this->actions = array('work_option', 'add_recruit', 'recruit_list', 'del_recruit', 'recruit_detail', 'add_job', 'job_list', 'del_job', 'job_detail');
        $this->postdata = $this->getRawBody();
        
        $this->recruit = D('Recruit' , 'Logic');  //初始化用户逻辑处理类
        $this->job = D('Job' , 'Logic');  //初始化用户逻辑处理类
        $this->roleController();
    }
    
    /**
     * 获取工作要求选项
     */
    function work_option(){
        $this->checkLogin();
        
        $this->return = $this->recruit->get_work_option($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 新增招聘信息
     * {"action":"add_recruit"}
     */
    function add_recruit(){
        $this->checkLogin();
        $this->return = $this->recruit->add_recruit($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取招聘信息列表
     * {"action":"recruit_list","uid":1,"page":1}
     */
    function recruit_list(){
        $this->return = $this->recruit->get_recruit_list($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 获取招聘信息详情
     * {"action":"recruit_detail","id":1}
     */
    function recruit_detail(){
        $this->return = $this->recruit->recruit_detail($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 删除招聘信息 
     * {"action":"del_recruit","id":1}
     */
    function del_recruit(){
        $this->checkLogin();
        $this->return = $this->recruit->del_recruit($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 求职信息列表
     * {"action":"job_list","uid":1}
     */
    function job_list(){
        $this->return = $this->job->get_job_list($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 发布求职信息
     */
    function add_job(){
        $this->checkLogin();
        $this->return = $this->job->add_job($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 求职信息详情
     */
    function job_detail(){
        $this->return = $this->job->get_job_detail($this->postdata);
        return $this->responseJson();
    }
    
    /**
     * 删除求职信息
     */
    function del_job(){
        $this->checkLogin();
        $this->return = $this->job->del_job($this->postdata);
        return $this->responseJson();
    }
}
