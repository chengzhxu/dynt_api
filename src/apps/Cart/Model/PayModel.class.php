<?php

namespace Cart\Model;

use Think\Model;

/**
 * Description of CommonVersionsModel
 *
 * @author Kevin
 */
class PayModel extends Model {
	protected $trueTableName = 'hjy_common_postion'; 

    public function pay($payArray){
		//import('Vendor.Ping.lib.Pingpp');
		$PAY = C('PAY');
		Vendor('Ping/init');
		$Pingpp = new \Pingpp();
		$Pingpp::setApiKey($PAY['api_key']);
		
		try {
			$ch = \Charge::create(
				$payArray
				//array(
					//'subject'   => 'Your Subject',
					//'body'      => 'Your Body',
					//'amount'    => $amount,
					//'order_no'  => $orderNo,
					//'currency'  => 'cny',
					//'extra'     => $extra,
					//'channel'   => $channel,
					//'client_ip' => $_SERVER['REMOTE_ADDR'],
					//'app'       => array('id' => 'app_1Gqj58ynP0mHeX1q')
				//)
			);
			return $ch;
		} catch (\Pingpp\Error\Base $e) {
			header('Status: ' . $e->getHttpStatus());
			return ($e->getHttpBody());
		}
    }

	public function webhooks(){
// 	    F('ping' , serialize($_SERVER));
		// POST 原始请求数据是待验签数据，请根据实际情况获取
		// $raw_data = file_get_contents('php://input');
		$raw_data = file_get_contents('php://input');
 		//F('ping1' , $raw_data);  
		// 签名在头部信息的 x-pingplusplus-signature 字段
		$signature = $_SERVER['HTTP_X_PINGPLUSPLUS_SIGNATURE'];
		// 请从 https://dashboard.pingxx.com 获取「Webhooks 验证 Ping++ 公钥」
		$pub_key_path = VENDOR_PATH . "Ping/data/rsa_public_key.pem";
        
		$result = $this->verify_signature($raw_data, $signature, $pub_key_path);
		if ($result !== 1) {
			 return ;
		}
		$input_data = json_decode($raw_data, true);
		if($input_data['type'] == 'charge.succeeded'){
			//TODO update database
			http_response_code(200);// PHP 5.4 or greater
			
			$order_no = $input_data['data']['object']['order_no'];
			$description = explode('|||',$input_data['data']['object']['description']);
			$order = M('Order')->where(array('order_num1' => $order_no , 'order_status' => 0))->find();
			
			
			if($order) {
			   //更新订单状态
			   //M('Order')->where( array('order_num1'=>$order_no) )->save(array('order_status'=>1,'pay_time'=>NOW_TIME,'order_num2'=>$input_data['data']['object']['transaction_no']));
			   $updateData['order_status']=1;
			   $updateData['pay_time']=NOW_TIME;
			   $updateData['order_num2']=$input_data['data']['object']['transaction_no'];
			   M('Order')->where( array('order_num1'=>$order_no) )->save($updateData);

// 			   $orderNew = M('Order')->where(array('order_num1' => $order_no , 'order_status' => 0))->find();
			   $packet_id = $order['packet_id'];
			   $uid = $order['uid'];
			   //设置红包已使用、更新用户零钱、记录零钱日志
			   setPacketMoney($packet_id,$uid,0);
				
			   
			   //提现活动
			   //objid福利社ID ,TODO
			   //获取用户所属组
				//F('uid' , $order);
			   $groupid = M('MemberProfile')->where(array('uid' => $uid))->getField('groupid');
			   //F('groupid' , $groupid.'###'.$packet_id);
 			   //$count = M('OrderGoods')->where(array('order_id' => $order['id'] , 'obj_id' => 923))->count();
 			   if(!$packet_id && $groupid == 8) { //没有使用红包,并且使用天使会员
 			       //说明购买了指定商品
 			       M('MemberCredits')->where( array('uid'=>$uid) )->setInc('credit3',58);
 			       //记录用户可提现零钱日志
 			       M('MoneyLogs')->add(		array('uid'=>$uid,'money'=>58,'dateline'=>NOW_TIME,'type'=>'credit3','remark'=>$order['order_num1'])
					);
 			   }
			} else {
			    return ;
			}

		}else if($input_data['type'] == 'refund.succeeded'){
			//TODO update database
			http_response_code(200);// PHP 5.4 or greater
		}else{
			//TODO update database
			http_response_code(500);// PHP 5.4 or greater
		}
	}

	// 验证 webhooks 签名
	private function verify_signature($raw_data, $signature, $pub_key_path) {
		$pub_key_contents = file_get_contents($pub_key_path);
		// php 5.4.8 以上，第四个参数可用常量 OPENSSL_ALGO_SHA256
		return openssl_verify($raw_data, base64_decode($signature), $pub_key_contents, 'sha256');
	}

}
