<?php

namespace Cart\Controller;
use Common\Controller\RestfulController;

/**
 * Description of DisplayController
 *
 * @author Kevin
 */
class DisplayController extends RestfulController{
    
    /**
     * 购物车列表
     */
    public function cart(){
       if(session('uid')){
			$this->display();
		}else{
			$this->redirect('/');
		}
    }

	/**
     * 支付页面
     */
    public function onlinepay(){
       if(session('uid')){
		   //如果是在微信浏览器内
		   if(is_weixin()) {
				//jsapi
				if(!$_GET['openid']) {
					$locationurl = '';
					header('Location:'. $locationurl);
					exit;
				}
				$this->assign('openid' , I('get.openid','0','strval'));
		   }else{
				$this->assign('openid' , 0);
		   }
		   $this->display();
		}else{
			$this->redirect('/');
		}
    }

	function pay(){
		$this->display();
	}
	
	/**
	 * 红包列表
	 */
	function getPacketList() {
	    $this->display();
	}
}
