<?php
namespace Welfare\Controller;
use Think\Controller;
use Org\Wechat;
/**
 * 福利社
 * @author Kevin
 *
 */
class DisplayController extends Controller {
	
	protected $wechat;

	public function __construct() {
		parent::__construct();
		import('Org.Wechat');
        $this->wechat = new Wechat(C('WX_CONFIG'));
	}

    /**
     * 福利社首页
     */
    function index() {
		$this->getJsticket();
        $this->display();
    }
	
	/**
     * 福利社详情
     */
	function detail() {
		$this->getJsticket();
        $this->display();
    }

	function getJsticket() {
		if(is_weixin()) {
			// 注意 URL 一定要动态获取，不能 hardcode.
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$ticket = $this->wechat->getJsSign($url,NOW_TIME);
			
			$this->assign('title' , '');
			$this->assign('desc' , '！');
			$this->assign('ticket' , $ticket);
		}
    }
    
}