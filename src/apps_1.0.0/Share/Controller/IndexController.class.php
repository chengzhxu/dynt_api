<?php

namespace Share\Controller;

/**
 * 分享类
 *
 * @author kevin
 */
class IndexController extends \Think\Controller{
    
    /**
     * 分享主方法
     */
    function index(){
        $type = I('type', 0);
        $id = I('id', 0);
        if($type && $id){
            switch ($type) {
                case 1:              //话题
                    $this->share_topic($id);
                    break;
                case 2:              //招聘
                    $this->share_recruit($id);
                    break;
                case 3:              //求职
                    $this->share_job($id);
                    break;
                default:
                    break;
            }
        }
    }
    
    /**
     * 话题分享
     */
    function share_topic($id){
        $topic = D('Topic/Topic', 'Logic');
        $return = $topic->get_topic_detail(array('topic_id' => $id));
        $result = array();
        if($return['code'] == 200){
            $result = $return['data'];
        }
        $this->assign('result', $result);
        $this->display('share_topic');
    }
    
    /**
     * 招聘分享
     */
    function share_recruit($id){
        $recruit = D('Work/Recruit', 'Logic');
        $return = $recruit->recruit_detail(array('id' => $id));
        $result = array();
        if($return['code'] == 200){
            $result = $return['data'];
        }
        $this->assign('result', $result);
        $this->display('share_recruit');
    }
    
    /**
     * 求职分享
     */
    function share_job($id){
        $job = D('Work/Job', 'Logic');
        $return = $job->get_job_detail(array('id' => $id));
        $result = array();
        if($return['code'] == 200){
            $result = $return['data'];
        }
        $this->assign('result', $result);
        $this->display('share_job');
    }
}
