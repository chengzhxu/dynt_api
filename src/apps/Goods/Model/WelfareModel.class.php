<?php

namespace Welfare\Model;

use Think\Model;

/**
 *
 * 福利社
 * @author Kevin 
 */
class WelfareModel extends Model {
    
    /**
     * 获取福利社首页 
	 * @param intval $type
     */
    function getIndex($data) {
		$return['code'] = 200;
		$return['message'] = '操作成功';
		$page = intval($data['page']) ? intval($data['page']) : 1;
        $offset = ($page - 1) * C('PAGESIZE');
		//今天的unixtime时间
		$nowtime = strtotime(date('Y-m-d' ,NOW_TIME));
		//获取今天的福利活动
		$field = '*';
		$where['pretime'] = array('between' , array($nowtime , $nowtime + 86400-1));
		$where['type'] = 1;
		$welfareTime = NOW_TIME;
		$today = $this->field($field)->where($where)->order('endtime asc')->limit($offset , C('PAGESIZE'))->select();
		if(!$today){
			$return['data'] = array();
			return $return;
		}
		$return['data'] = $this->todayStatusApi( $today,array('id','title','img','detailed_img') );
        return $return;
    }
	
	
    
    /**
     * 获取福利详情
     * @param unknown $data
     * @return number
     */
    function getDetail($data) {
        if(!$data['id'])
            return -1;
        M('Welfare')->where( array('id'=>$data['id']) )->setInc('view_count');
		$today = $this->where(array('id'=>$data['id']))->select();
		//获取福利社相关信息
		$todayBuy = $this->todayStatusApi( $today,array('id','title','title2','img','detailed_img','marketprice','content') );
		$todayBuy[0]['img'] = $todayBuy[0]['detailed_img'];
		//获取购物车数量
		$cartCount = D('Cart/CartWeb')->cartCount(0);
	
		$data = array('data'=>$todayBuy[0],'code'=>200);

		return $data;

        
        
    }

	 /**
	 * 获取福利社阶段(API)
	 * @param array $today  今日福利数组
	 * @param array $field  返回字段
	 */
	 public function todayStatusApi($today,$field){
			$welfarePrice = get_select('WELFARE_FREE_PRICE');
			$todayBuy = array();
			$Order = D('Cart/Order');
			$one = $two = array();
			$starttime = strtotime(date('Y-m-d' , NOW_TIME));
			$mapCart['uid'] = UID;
			$mapCart['obj_type'] = 0;
			$mapCart['obj_status'] = 1;
			$mapCart['dateline'] = array('egt',$starttime); 
			foreach($today as $key=>$value){
				$todayBuy['market_price'] = $value['marketprice'];
				
				$orderSaleCount = $value['sale_count']-$Order->getOrderCount(0,$value['id'],1);
				$mapCart['obj_id'] = $value['id'];
				$cartNum = M('CartWeb')->where( $mapCart )->getField('goods_count');
				foreach($field as $k=>$v){
					$todayBuy[$v] = $value[$v];
				}
				$todayBuy['buy_count'] = $orderSaleCount;
				$todayBuy['price'] = $value['sale_price'];
				$todayBuy['cart_num'] = intval($cartNum);
				$todayBuy['img'] = str_replace('a.oss','img.cdn',$value['img']).'@!480x480';
				$todayBuy['detailed_img'] = str_replace('a.oss','img.cdn',$value['detailed_img']).'@!480x480';
				$return[] = $todayBuy; 
				$buy_count[] = $orderSaleCount;
			}
			array_multisort($buy_count, SORT_DESC, $return);
			return $return;
	 }

	 /**
	 * 获取福利社阶段信息
	 * @param array $wid        福利社ID
	 * @param array $objStatus  阶段状态(0:0元抢购时段;1:打折时段;2:正常购买)
	 */
	 public function getStatusInfo($wid,$objStatus){
		$welfarePrice = get_select('WELFARE_FREE_PRICE');
		$statusInfo = M('Welfare')->where( array('id'=>$wid) )->find();

		$Order = D('Cart/Order');
		$info['price']     =  $statusInfo['sale_price'];
		$info['buy_count'] =  $statusInfo['sale_count'] - $Order->getOrderCount(0,$statusInfo['id'],1,0);
		
		$info['title'] = $statusInfo['title'];
		$info['id'] = $statusInfo['id'];
		$info['product_id'] = $statusInfo['product_id'];
		$info['condition'] = $statusInfo['condition'];
		return $info;
	 }
         
}
