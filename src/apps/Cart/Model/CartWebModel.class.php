<?php

namespace Cart\Model;

use Think\Model;

/**
 * 购物车类
 * @author kevin
 */
class CartWebModel extends Model {

    /**
     * 加入购物车
	 * @param intval $obj_type   来源(0:福利社)
	 * @param intval $obj_id     来源ID
     */
	public function addCart($data){
		
	    //查看该商品的状态
	    $product = M('welfare')->where(array('id' => $data['obj_id']))->limit(1)->select();
	    
	    if(!$product)
	        return -4;  //福利社不存在
        
	    $cartList = session('cartList');
	    $cartcount = intval($cartList[$data['obj_id']]);
	    $buy_count = $product[0]['sale_count'] - D('Cart/Order')->getOrderCount(0,$data['obj_id'],1);
	    
	    if($cartcount >= $buy_count)
	        return -5;
	    
    	    //查看用户当天是否有加入购物车
    	    $starttime = strtotime(date('Y-m-d' , NOW_TIME));
    	    $endtime   = $starttime + 86400;
    	    $where['uid'] = UID;
    	    $where['obj_id'] = $data['obj_id'];
    	    $where['obj_type'] = intval($data['obj_type']);
    	    $where['dateline'] = array('between' , array($starttime , $endtime));
    	    $count = $this->field('sum(goods_count) as counts')->where($where)->find();
    	    if($count['counts'] == 0) {
        	    $insertdata = array(
        	        'uid' => UID,
        	        'obj_id' => $data['obj_id'],
        	        'obj_type' => intval($data['obj_type']),
        	        'dateline' => NOW_TIME,
        	        'goods_count' => $data['goods_count'] ? $data['goods_count'] : 1,
        	        'obj_status'  => 1
        	    );
        	    $this->add($insertdata);
    	    } else {
    	        $updatedata = array(
        	        'dateline' => NOW_TIME,
        	        'goods_count' => $count['counts']+1,
        	        'obj_status'  => 1
        	    );
				$this->where( $where )->save($updatedata);
    	    }
			return $this->cartCount(0);

	}
        
     /**
     * 获取购物车列表
     */
    function getCartListByUid($data){
        $welfarePrice = get_select('WELFARE_FREE_PRICE');
        $address = M('member_address')->where("uid = " . UID)->order('is_default desc')->find();   //用户默认收货地址
		$$address = $address ? $address : array();
        
		$credit = 0;
        $isH5 = 0;
        $starttime = strtotime(date('Y-m-d' , NOW_TIME));
		$products = '';
        $condition = '';
        $totalamount = 0;
        
        $where = ' (a.uid = '. UID .' and b.type = 1 ) or (b.type = 0 and a.uid =  '.UID.' and a.dateline > '.$starttime.')';
        $cartList = M('CartWeb')->alias('a')->join('hjy_welfare as b on a.obj_id = b.id')->where($where)->field('a.id, a.obj_id, a.obj_type, a.goods_count, a.obj_status,a.dateline,b.type,b.endtime')->select();
		
        $Order = D('Cart/Order');
        foreach ($cartList as $key=>$val){
            if($val['obj_type'] == 0){    //福利社
				$welfare = M('welfare')->where("id = " . $val['obj_id'])->field('id,title, img, prize, marketprice, sale_price, starttime, sale_time, sale_count,product_id,condition')->find();
				$cartList[$key]['prize'] = $welfare['prize'];
				$cartList[$key]['marketprice'] = floatval($welfare['marketprice']);  
				$cartList[$key]['price'] = floatval($welfare['sale_price']);
				$cartList[$key]['title'] = $welfare['title'];
				$cartList[$key]['img'] = $welfare['img'];
				
				$cartList[$key]['buy_count'] = $welfare['sale_count']-$Order->getOrderCount(0,$val['obj_id'],1);
				//连接产品ＩＤ
				if($welfare['product_id']) {
					if($products)
						$products .= ',';
					$products .= $welfare['product_id'];
				}
				
				//条件
				if($welfare['condition']) {
					if($condition)
						$condition .= ',';
					$condition .= $welfare['condition'];
				}
				
				$totalamount += $welfare['sale_price'] * $val['goods_count'];  //总金额累加
				}
        }
        
		$userinfo = getUserInfo(UID,2);
		$where = array(
		    'gid'       => $products,
		    'condition' => $condition,
		    'amount'    => $totalamount,
		    'packet_id' => $data['packet_id'],
		);
		$packets = D('Packets')->getPacketsList($where);

        $result = array('cartList' => $cartList, 'address' => $address, 'packets'=>$packets);
        
        $return['code'] = 200;
        $return['message'] = '';
        $return['data'] = $result;
        
        return $return;
    }
	
	/**
     * 删除购物车
     */
	function delCart($data){
		//判断购物车合法性
		$isSelf = M('CartWeb')->where( array('uid'=>UID,'id'=>$data['id']) )->count();
		if($isSelf){
			M('CartWeb')->delete($data['id']);
		}
		$return['code'] = 200;
        $return['message'] = '';
        $return['data'] = array();
		$this->cartCount(0);
		return $return;
	}

	/**
     * 获取购物车数量
	 * @param intval 来源类型(0:福利社)
     */
	public function cartCount($objType){
		$map['obj_type'] = $objType;
		if(UID){
			$map['uid'] = UID;
			$map['dateline'] = array( 'egt',strtotime(date('Y-m-d',NOW_TIME)) );
			$sumGoods = M('CartWeb')->field('sum(goods_count) as sum')->where( $map )->find();
			session('cartcount',intval($sumGoods['sum']));
			$welfare = D('Welfare/Welfare');
			$welList = $welfare->getIndex();
			if($welList){
				foreach($welList['data'] as $value){
					$cartSession[$value['id']] = $value['cart_num'];
				}
				session('cartList',$cartSession);
			}
		}else{
			session('cartcount',0);
		}
		return $sumGoods['sum'];
	 }

	 /**
     * 增减购物车商品数量
     */
	public function changeGoods($data){
		if(UID){
			if($data['amount'] == 1){
				M('CartWeb')->where( array('id'=>$data['id']) )->setInc('goods_count');
			}else{
				$goodscount = M('CartWeb')->where( array('id'=>$data['id']) )->getField('goods_count');
				if($goodscount>=1){
					M('CartWeb')->where( array('id'=>$data['id']) )->setDec('goods_count');
				}
			}
			$map['obj_type'] = $data['obj_type'];
			$map['uid'] = UID;
			$map['dateline'] = array( 'egt',strtotime(date('Y-m-d',NOW_TIME)) );
			$sumGoods = M('CartWeb')->field('sum(goods_count) as sum')->where( $map )->find();
			session('cartcount',intval($sumGoods['sum']));
		}else{
			session('cartcount',0);
		}
		$return['code'] = 200;
        $return['message'] = '';
        $return['data'] = array();
		return $return;
	 }

}
