<?php

namespace Cart\Controller;
use Common\Controller\RestfulController;

/**
 * 公共接口
 *
 * @author Kevin
 */
class IndexController extends RestfulController {

    protected $cart;
    protected $postdata;
    
    function _initialize() {
        //定义action
        parent::_initialize();
        $this->postdata = I('post.');
        
        $this->cart = D('CartWeb');
    }
    
    /**
     * 加入购物车
     * post 参数{"obj_type":0,"obj_id":12,"goods_count":2}
     */
    public function addCart() {
        
        $this->checkLogin();
        
        $return = $this->cart->addCart($this->postdata);
        
        if(-1 == $return) {
            $this->return['code'] = 1101;
        } elseif(-2 == $return) {
            $this->return['code'] = 1102;
        } elseif(-3 == $return) {
            $this->return['code'] = 1103;
        } elseif(-4 == $return) {
            $this->return['code'] = 1104;
        } elseif(-5 == $return) {
            $this->return['code'] = 1110;
        } else {
            $this->return['code'] = 200;
			$this->return['data'] = array('cart_count'=>$return);
        }
        $this->responseJson();
    }

	/**
     * 增减购物车商品数量
     * post 参数{"id":12,"amount":1,"obj_type":0}
     */
    public function changegoods() {
        
        $this->checkLogin();
        
        $return = $this->cart->changeGoods($this->postdata);
        $this->responseJson();
    }
    
    /**
     * 获取购物车列表
     */
    public function getCartList(){
        
        $this->checkLogin();
        $this->return = $this->cart->getCartListByUid($this->postdata);
        $this->responseJson();
    }
	
	/**
     * 验证购物车商品
     */
	public function getVerifyCart(){
		$this->checkLogin();
		
        $this->return = D('Order')->verifyCart($this->postdata);
		$this->responseJson();

	}

	/**
     * 删除购物车
	 * post 参数{"id":1}
     */
	public function delCart(){
		$this->checkLogin();
        $this->return = $this->cart->delCart($this->postdata);
		$this->responseJson();

	}
	
	/**
	 * 我的红包
	 */
	public function getPackets() {
	    $this->checkLogin();
	    
		$data = $this->cart->getCartListByUid(array('packet_id' => false));
	    //$data= D('Cart/Packets')->getPacketsList(array('amount'=>0,'condition'=>false,'gid'=>false,'packet_id'=> false));
	    $ndata = $data['data']['packets'];
		
	    foreach($ndata['return'] as $key=>$value){
	        $newData[$key]['money'] = $value['money'];
	        $newData[$key]['over_time'] = date('Y-m-d',$value['over_time']);
	        $newData[$key]['name'] = $value['name'];
	        $newData[$key]['min_goods_amount'] = $value['min_goods_amount'];
	        $newData[$key]['start_time'] = date('Y-m-d',$value['get_time']);
	        $newData[$key]['falsenum'] = $value['falsenum'];
	        $newData[$key]['id'] = $value['id'];
	        $newData[$key]['status'] = $value['status'];
	        if(!$value['message']){
	            $value['message'] = array();
	        }
	        $newData[$key]['text'] = $value['message'];
	        	
	    }
		
	    $this->return = array('data'=>$newData,'code'=>200 , 'firstData' => $ndata['firstData']);
	    $this->responseJson();
	}
	
	public function webhooks(){
		$this->cart->webhooks();
	}
    
}
