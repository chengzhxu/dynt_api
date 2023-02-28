<?php

namespace Cart\Controller;
use Think\Controller;
use Org\Wechat;
/**
 * 公共接口
 *
 * @author Kevin
 */
class PayController extends Controller {
    
    public $wechat;
    
	public function webhooks(){
		D('Pay')->webhooks();
	}

	public function result(){
	    
	    import('Org.Wechat');
        $this->wechat = new Wechat(C('WX_CONFIG'));
        $config = C('WX_CONFIG');
        $this->wechat->set_access_token(S('wechat_access_token' . $config['appid']));
        $this->getJsticket();
        
        $order = M('Order')->where(array('order_num1' => I('out_trade_no') , 'order_status' => 1))->find();
        $count = M('OrderGoods')->where(array('order_id' => $order['id'] , 'obj_id' => 923))->count();
        $userinfo = getUserInfo($order['uid'] , 2);
        
        if($count > 0 && !$order['packet_id'] && $userinfo['profile']['groupid'] == 8) {
            $this->assign('show' , 1);
        } else 
            $this->assign('show' , 0);
        
		$this->display();
	}
	
	function getJsticket() {
	    // 注意 URL 一定要动态获取，不能 hardcode.
	    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	    $ticket = $this->wechat->getJsSign($url,NOW_TIME);
	
	    $this->assign('ticket' , $ticket);
	}
    
}
