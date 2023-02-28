<?php

namespace Publics\Controller;

use Common\Controller\RestfulController;

/**
 * 通用上传
 *
 * @author Kevin
 */
class UploadController extends RestfulController {

    protected $postdata;
    protected $actions;
    
    public function _initialize() {

        parent::_initialize();
        
        //定义action
        $this->actions = array('upload');
        $this->postdata = $this->getRawBody();
        
        $this->roleController();
    }

    /**
     * 上传图片
     * @param img base64格式图片
     * @return 返回上传图片地址
     * 
     * {"action":"upload","img":""}
     * 
     * {
            "code": 200,
            "message": "操作成功",
            "data":
            {
                "url": ""
            }
        }
     */
    function upload() {
        
        $this->checkLogin();
        
        if(!$this->postdata['img'])
            $this->return['code'] = 302;  //参数错误
        else {
            //初始化 oss服务
            $oss = D('Common/Oss' , 'Logic');
            
            $return = $oss->save($this->postdata['img'], 'attachment/' . date('Y-m-d', NOW_TIME));
            if($return['status'] == 200) {
                $this->return['code'] = 200;
                $this->return['data'] = array('url' => $return['url']);
            } else {
                $this->return['code'] = 440;
            }
        }
        $this->responseJson();
    }

}
