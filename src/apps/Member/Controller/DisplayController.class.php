<?php
namespace Member\Controller;
use Think\Controller;

/**
 * 福利社
 * @author Kevin
 *
 */
class DisplayController extends Controller {
    /**
     * 用户登录
     */
    function login() {
        $this->display();
    }

	/**
     * 用户收货地址列表
     */
    function address() {
		if(session('uid')){
			$this->display();
		}else{
			$this->redirect('/');
		}
    }

	/**
     * 新增收货地址
     */
    function addaddress() {
		if(session('uid')){
			$this->display();
		}else{
			$this->redirect('/');
		}
    }

	/**
     * 修改收货地址
     */
    function editaddress() {
		if(session('uid')){
			$this->display();
		}else{
			$this->redirect('/');
		}
    }
	
	
  
    
    
}