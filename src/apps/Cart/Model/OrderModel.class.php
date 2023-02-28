<?php

namespace Cart\Model;

use Think\Model;

/**
 * 订单类
 * @author Kevin
 */
class OrderModel extends Model {

    /**
     * 获取购买成功的商品数
	 * @param intval $obj_type   来源(0:福利社)
	 * @param intval $obj_id     来源ID
	 * @param intval $obj_status 购买时段(0:0元抢购时段;1:打折时段;2:正常购买)
	 * @param intval $is_gopay   是否为支付动作(0:去支付；1：不是支付)
     */
	public function getOrderCount($obj_type,$obj_id,$obj_status,$is_gopay = 1){
		$sql = "select sum(g.goods_count) as ddcount from hjy_order o join hjy_order_goods g on o.id = g.order_id where g.obj_type = $obj_type AND g.obj_status = $obj_status AND g.obj_id = $obj_id and (o.order_status between 1 and 3)";
		$sum = M('Order')->query($sql);
		return intval($sum[0]['ddcount']);
	}

	/**
     * 获取我的订单
	 * @param intval $uid   用户ID
     */
	 public function myOrder($data){
		$page = intval($data['page']) ? intval($data['page']) : 1;
        $offset = ($page - 1) * C('PAGESIZE');
		//获取订单
		$orderList = M('Order')
					->where( array('uid'=>UID) )
					->field('id,order_num1,express_name,express_num,order_status,dateline')
					->order('order_status asc,dateline desc')
					->limit($offset , C('PAGESIZE'))
					->select();
		//获取订单下产品
		foreach($orderList as $key=>$value){
			$orderList[$key]['dateline'] = date('Y-m-d',$value['dateline']);
			$orderList[$key]['goods'] = $this->orderGoods($value['id'],$orderList[$key]['dateline'],1); 
		}

		return $orderList;

		
	 }
	
	 /**
     * 获取订单下的产品
	 * @param intval $orderid   订单ID
	 * @param intval $dateline  订单时间
	 * @param intval $type      订单类型(1:福利社)
     */
	 private function orderGoods($orderid,$dateline,$type){
		$welfarePrice = get_select('WELFARE_FREE_PRICE');
		$table = array(1=>'welfare');
		$orderGoods = M('OrderGoods')->where( array('order_id'=>$orderid) )->field('order_id,obj_id,obj_status,goods_count')->select();
		foreach($orderGoods as $key=>$value){
			if($value['obj_type'] == 0){
				$welfare = M($table[$type])->where( array('id'=>$value['obj_id']) )->field('title,img,type,marketprice,sale_price,product_id,condition')->find();
				$orderGoods[$key]['thumbs'] = $welfare['img'];
				$orderGoods[$key]['title'] = $welfare['title'];
				$orderGoods[$key]['dateline'] = $dateline;
				$orderGoods[$key]['product_id'] = $welfare['product_id'];
                $orderGoods[$key]['condition'] = $welfare['condition'];

				if($welfare['type'] == 1){
					$orderGoods[$key]['price'] = floatval($welfare['marketprice']);
				} else {
					if($value['obj_status'] == 0){
						$orderGoods[$key]['price'] = floatval($welfarePrice[0]);
					}elseif($value['obj_status'] == 1){
						$orderGoods[$key]['price'] = floatval($welfare['sale_price']);
					}else{
						$orderGoods[$key]['price'] = floatval($welfare['marketprice']);
					}
				}
				
			}
		}
		return $orderGoods;
	 }

	 /**
     * 获取购物车有效性
	 * @param array $cartid   购物车信息
     */
	public function verifyCart($data){
	    
		$PAY = C('PAY');
// 		$str = '{"cart":[{"cart_id":"1","goods_count":"3"},{"cart_id":"2","goods_count":"2"}]}'; //$data['is_credit']   $data['address_id']   $data['channel']
		$cart = $data['cart'];
		$errorMessage = '';
		$goodsCount = 0;
		$isH5 = 0;
		$packet_id = intval($data['packet_id']);	  //红包ID
		if($packet_id){
		    $where = array(
		        'uid' => UID,
		        'is_used' => 0,
		        'over_time' => array('egt' , NOW_TIME),
		        'id'        => $packet_id,
		    );
			$redPackets = floatval(M('RedPackets')->where( $where )->getField('money')); //红包金额
			if(!$redPackets){
			    $return['code'] = 1114;
			    $return['data'] = array();
			    return $return;
			}
		}else{
			$redPackets = 0;
		}
		foreach( $cart as $key=>$value){
			//获取购物车信息
			$cartInfo = M('CartWeb')->where(array('id'=>$value['id']))->find();
			if($cartInfo){
				//福利社购物车
				    $welfare = D('Welfare/Welfare');
					$orderGoods[] = array(
						'obj_type'=>0,
						'obj_id'=>$cartInfo['obj_id'],
						'obj_status'=>$cartInfo['obj_status'],
						'goods_count'=>$value['goods_count']
					);
					$cart_ids[] = $value['id'];
					$statusInfo = $welfare->getStatusInfo($cartInfo['obj_id'],$cartInfo['obj_status']);
					$goodsCount = $value['goods_count'] + $goodsCount; //获取商品总数
					$goodsPrice = ($statusInfo['price']*$value['goods_count']) + $goodsPrice; //获取商品总价
					$goodsTitle[]= $statusInfo['title']; //获取商品名
					$goodsId[]   = $statusInfo['id']; //获取商品ID
					
					if( $statusInfo['buy_count'] < $value['goods_count']){
						//$errorMessage .= $statusInfo['title'].":剩余数量不足==";
						$errorMessage = 1;
						M('OrderFailure')->add( array('obj_type'=>0,'obj_id'=>$statusInfo['id'],'uid'=>UID,'dateline'=>NOW_TIME,'status'=>1) );
						$return['code'] = 1108;
						$return['data'] = array();
						return $return;
					}


					//连接产品ＩＤ
					if($statusInfo['product_id']) {
					    if($products)
					        $products .= ',';
					    $products .= $statusInfo['product_id'];
					}
					
					//条件
					if($statusInfo['condition']) {
					    if($condition)
					        $condition .= ',';
					    $condition .= $statusInfo['condition'];
					}

			}else{
				//订单不存在
				$return['code'] = 1105;
				$return['data'] = array();
				return $return;
			}

		}
		if($errorMessage == ''){//订单有效
		    $express = $PAY['express_price'];
			$goodsPrice = ( $goodsPrice-$redPackets )*100;
			if($goodsPrice<=0){
				$order_status = 1;
				$amount = 0 ; //TODO $express
				$data['channel'] = 'niaoting';
				$pay_time = NOW_TIME;
			}else{
				$order_status = 0;
				$pay_time = 0;
				$amount = number_format($goodsPrice / 100 , 2) ;//TODO $express
			}
			
			//判断选定红包是否有效 2016/02/17
			if($packet_id) {
			    $where = array(
			        'gid'       => $products,
			        'condition' => $condition,
			        'amount'    => $amount,
			    );
			    $packet_status = 0;
			    $packets = D('Packets')->getPacketsList($where);
			    if($packets['number'] == 0) {
			        $return['code'] = 1115;
			        $return['data'] = array();
			        return $return;
			    } else {
			        if($packets['return']) {
			            foreach($packets['return'] as $v) {
			                if($v['id'] == $packet_id) {
			                    $packet_status = 1;
			                    break;
			                }
			            }
			        }
			    }
			    if($packet_status == 0) {
			        $return['code'] = 1115;
			        $return['data'] = array();
			        return $return;
			    }
			}
			//插入订单表(hjy_order,hjy_order_goods)
			$order = M('Order');
			$orderNum = orderNum(UID,$goodsCount,'F');
			$orderdata = array(
				'order_num1'=>$orderNum,
				'order_status'=>$order_status,
				'dateline'=>NOW_TIME,
				'uid'=>UID,
				'deleted'=>0,
				'address_id'=>$data['address_id'],
			    'channel'   => $data['channel'],
			    'amount'    => $amount,
				'credit'=>0,
			    'express' => intval($express),
				'remark'=>$data['remark'],
				'from'=>'h5shop',
			    'small_money'	=>0,
			    'red_packets'	=>$redPackets,
			    'packet_id'		=>$packet_id,
			    'pay_time'      => $pay_time,

			);
			//在order模型中启动事务
			$order->startTrans();
			$orderid = $order->add($orderdata);
			foreach($orderGoods as $key=>$value){
				$orderGoods[$key]['order_id'] = $orderid;
			}

			$goods = M('OrderGoods')->addAll($orderGoods);
			
			if ($orderid && $goods){
			    
			    //删除购物车
			    $delCartMap['id'] = array('in',$cart_ids);
			    M('CartWeb')->where( $delCartMap )->delete();
				D('CartWeb')->cartCount(0);
// 			    F('ping' , serialize($cart_ids));
				// 提交事务
				$order->commit(); 
				if($goodsPrice<=0){
					$return['code'] = 1111;
					$return['data'] = array('order_no' => $orderNum);
					//设置红包已使用、更新用户零钱、记录零钱日志
					setPacketMoney($packet_id,UID,0);
					return $return;
				}
				if( $isH5 ){
					$payBody = '鸟听';
				}else{
					$payBody = implode(',', $goodsTitle) . '|||' . implode(',', $goodsId);
				}
				if($data['channel'] == 'wx_pub'){
					$payArray = array(
						'subject'   => '鸟听订单:'.$orderNum,
						'body'      => $payBody,
						'amount'    => $goodsPrice,
						'order_no'  => $orderNum,
						'currency'  => 'cny',
						'extra'     => array('open_id'=>$data['openid']),
						'channel'   => $data['channel'],
						'client_ip' => $_SERVER['REMOTE_ADDR'],
						'app'       => array('id' => $PAY['app_id'])
					);
					
				}else{
					$payArray = array(
						'subject'   => '鸟听订单:'.$orderNum,
						'body'      => $payBody,
						'amount'    => $goodsPrice,
						'order_no'  => $orderNum,
						'currency'  => 'cny',
						'extra'     => array('success_url'=>$PAY['success_url']),
						'channel'   => $data['channel'],
						'client_ip' => $_SERVER['REMOTE_ADDR'],
						'app'       => array('id' => $PAY['app_id'])
					);
				}
				//print_r($payArray);exit;
				$payreturn = D('Cart/Pay')->pay($payArray);
				
				$return['code'] = 200;
				$return['data'] = $payreturn;
				return $return; 
			}else{
			   // 事务回滚
			   $order->rollback(); 
			   $return['code'] = 399;
			   $return['data'] = array();
			   return $return;
			}
		}else{
			$return['code'] = 1106;
			$return['data'] = array();
			$return['errorMessage'] = $errorMessage;
			return $return;
		}

		
	}

}
