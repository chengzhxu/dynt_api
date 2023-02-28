<?php

namespace Util\Controller;

/**
 * 缓存相关
 *
 * @author kevin
 */
class CacheController extends \Think\Controller{
    function index(){
        $name = I('name', '');
        $data = S($name);
        print_r($data);
        print_r(S($name));exit;
        if($type == 'del'){
            S($name , null);
            $this->assign('data' , '删除成功');
        }else{
            $data = S($name);
            $this->assign('data' , $data);
        }
        $this->assign('name', $name);
        $this->display();
    }
    
    /**
     * 获取用户缓存
     */
    function getMemberCache(){
        $name = I('name', '');
        $data = S($name);
        print_r($data);
//        return array('code' => 200, 'data' => $data);
    }
    
    /**
     * 清空用户缓存
     */
    function emptyMemberCache(){
        $name = I('name', '');
        S($name, null);
        print_r(array('code' => 200));
    }
}
