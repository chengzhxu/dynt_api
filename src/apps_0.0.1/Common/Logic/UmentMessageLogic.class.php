<?php

namespace Common\Logic;

/**
 * 友盟消息推送接口
 *
 * @author floy
 */
class UmentMessageLogic {

    protected $config, $appMasterSecret;
    protected $postUrl, $postBody;
    protected $productionMode = false;

    /**
     * 初始化配置参数
     */
    function __construct() {
        $this->config = array(
            'ios' => array(
                'app_key' => '535a033256240b914b0001c1',
                'app_master_secret' => 'fztduhse814kegujsliriuhgsvprwr7m',
            ),
            'android' => array(
                'app_key' => '5376f8be56240b8c3b04b0c7',
                'app_master_secret' => 'wz2lypwa2s3ts1bycoesiw3smxfkgwrx',
            ),
            'alias_type' => 'UID'
        );
    }

    /**
     * 推送单个通知或消息
     * 具体参数,查看文档：http://dev.umeng.com/push/ios/api-doc#2_1_3
     * @param $data['display_type'] = 'notification/message' (必填)消息类型：通知或消息
     * @param $data['uid']		(必填)推送目标用户uid
     * @param $data['title']	(必填)消息标题
     * @param $data['text']		(android必填)消息内容
     * @param $data['after_open']['type'] 点击"通知"的后续行为，默认为打开app:go_app
     * @param $data['after_open']['value'] 
     * @param $data['extra']	自定义参数 :{key => value的格式}
     */
    function sendCustomizedcast($data) {
        if (isset($data['uid']) && isset($data['title']) && isset($data['text'])) {
        	$android = $this->sendAndroidCustomizedcast($data);	//Android
        	$ios = $this->sendIOSCustomizedcast($data);			//IOS
        	if ($android OR $ios) {
        		return TRUE;
        	}
        }
    }

    /**
     * 推送广播通知
     */
    function sendBroadcast($data) {
    	if (isset($data['uid']) && isset($data['title']) && isset($data['text'])) {
    		$android = $this->sendAndroidBroadcast($data);	//Android
        	$ios = $this->sendIOSBroadcast($data);			//IOS
//         	if ($ios) {
    		if ($android OR $ios) {
        		return TRUE;
        	}
    	}
    }

    /**
     * 推送群组用户信息
     */
    function sendGroupcast($data) {
    	
    }
    
    /**
     * 推送单个通知或消息到Android
     */
    function sendAndroidCustomizedcast($data) {
    	vendor('Umeng.android.AndroidCustomizedcast');
    	$customizedcast = new \AndroidCustomizedcast();
    	
		$customizedcast->setAppMasterSecret($this->config['android']['app_master_secret']);
		$customizedcast->setPredefinedKeyValue('appkey',           $this->config['android']['app_key']);
		$customizedcast->setPredefinedKeyValue('timestamp',        NOW_TIME);
		$customizedcast->setPredefinedKeyValue('alias',            md5($data['uid'].'NIAOTING'));
		$customizedcast->setPredefinedKeyValue('alias_type',       $this->config['alias_type']);
		$customizedcast->setPredefinedKeyValue('ticker',           $data['title']);
		$customizedcast->setPredefinedKeyValue('title',            $data['title']);
		$customizedcast->setPredefinedKeyValue('text',             $data['text']);
		
		//点击"通知"的后续行为，默认为打开app
		if (isset($data['after_open']['type'])) {
			$customizedcast->setPredefinedKeyValue('after_open',	$data['after_open']['type']);
			if ($data['after_open']['type'] == 'go_url') {
				$customizedcast->setPredefinedKeyValue('url',		$data['after_open']['value']);
			} elseif ($data['after_open']['type'] == 'go_activity') {
				$customizedcast->setPredefinedKeyValue('activity',	$data['after_open']['value']);
			} elseif ($data['after_open']['type'] == 'go_custom') {
				$customizedcast->setPredefinedKeyValue('custom',	$data['after_open']['value']);
			}
		} else {
			$customizedcast->setPredefinedKeyValue('after_open',	'go_app');
		}
		
		//自定义参数
		if (isset($data['extra'])) {
			foreach ($data['extra'] as $key => $value) {
				$customizedcast->setExtraField($key, $value);
			}
		}
		
		//消息类型是message
    	if ($data['display_type'] == 'message') {
			$customizedcast->setPredefinedKeyValue('display_type',	'message');
			$customizedcast->setPredefinedKeyValue('custom',		json_encode($data['extra']));
		}
		
		return $customizedcast->send();
    }
    
	/**
     * 推送单个通知或消息到IOS
     */
    function sendIOSCustomizedcast($data) {
    	vendor('Umeng.ios.IOSCustomizedcast');
    	$customizedcast = new \IOSCustomizedcast();
    	
		$customizedcast->setAppMasterSecret($this->config['ios']['app_master_secret']);
		$customizedcast->setPredefinedKeyValue("appkey",			$this->config['ios']['app_key']);
		$customizedcast->setPredefinedKeyValue("timestamp",			NOW_TIME);
		$customizedcast->setPredefinedKeyValue("alias",				md5($data['uid'].'NIAOTING'));
		$customizedcast->setPredefinedKeyValue("alias_type",		$this->config['alias_type']);
		$customizedcast->setPredefinedKeyValue("alert",				$data['title']);
		$customizedcast->setPredefinedKeyValue("badge",				1);
		$customizedcast->setPredefinedKeyValue("sound",				"chime");
		
		if (isset($data['after_open']['type']) && $data['after_open']['type'] == 'go_activity') {
			if (isset($data['extra']['myMsgCount'])) $arr['myMsgCount'] = $data['extra']['myMsgCount'];
			if (isset($data['extra']['myTestMsgCount'])) $arr['myTestMsgCount'] = $data['extra']['myTestMsgCount'];
			if (isset($data['extra']['objtype'])) $arr['objtype'] = $data['extra']['objtype'];
			if (isset($data['extra']['objid'])) $arr['objid'] = $data['extra']['objid'];
			if (isset($data['extra']['goType'])) $arr['goType'] = $data['extra']['goType'];
			if (isset($data['extra']['goTypeID'])) $arr['goTypeID'] = $data['extra']['goTypeID'];
			
			$customizedcast->setPredefinedKeyValue('care',		json_encode($arr));
		}
		
		$customizedcast->setPredefinedKeyValue('production_mode',	$this->productionMode);
		
		$customizedcast->setPredefinedKeyValue('message',	$data['text']);
		

    	return $customizedcast->send();
    }
    
    /**
     * 推送广播到Android
     */
    function sendAndroidBroadcast($data) {
    	vendor('Umeng.android.AndroidBroadcast');
    	$brocast = new \AndroidBroadcast();
    	
    	$brocast->setAppMasterSecret($this->config['android']['app_master_secret']);
    	$brocast->setPredefinedKeyValue('appkey', $this->config['android']['app_key']);
    	$brocast->setPredefinedKeyValue('timestamp', NOW_TIME);
    	$brocast->setPredefinedKeyValue('ticker', $data['title']);
    	$brocast->setPredefinedKeyValue('title', $data['title']);
    	$brocast->setPredefinedKeyValue('text', $data['text']);
    	
    	//点击"通知"的后续行为，默认为打开app
    	if (isset($data['after_open']['type'])) {
    	    $brocast->setPredefinedKeyValue('after_open',	$data['after_open']['type']);
    	    if ($data['after_open']['type'] == 'go_url') {
    	        $brocast->setPredefinedKeyValue('url',		$data['after_open']['value']);
    	    } elseif ($data['after_open']['type'] == 'go_activity') {
    	        $brocast->setPredefinedKeyValue('activity',	$data['after_open']['value']);
    	    } elseif ($data['after_open']['type'] == 'go_custom') {
    	        $brocast->setPredefinedKeyValue('custom',	$data['after_open']['value']);
    	    }
    	} else {
    	    $brocast->setPredefinedKeyValue('after_open',	'go_app');
    	}
    	
    	$brocast->setPredefinedKeyValue('production_mode', $this->productionMode);
    	$brocast->setExtraField('test', 'helloworld');
    	
    	return $brocast->send();
    }
    
    /**
     * 推送广播到IOS
     */
    function sendIOSBroadcast($data) {
    	vendor('Umeng.ios.IOSBroadcast');
    	$brocast = new \IOSBroadcast();
    	
    	$brocast->setAppMasterSecret($this->config['ios']['app_master_secret']);
    	$brocast->setPredefinedKeyValue('appkey', $this->config['ios']['app_key']);
    	$brocast->setPredefinedKeyValue('timestamp', NOW_TIME);
    	$brocast->setPredefinedKeyValue('alert', $data['title']);
    	$brocast->setPredefinedKeyValue('badge', 0);
    	$brocast->setPredefinedKeyValue('sound', 'chime');
    	$brocast->setPredefinedKeyValue('production_mode', $this->productionMode);
    	$brocast->setCustomizedField('test', 'helloworld');
    	
    	return $brocast->send();
    }

    /**
     * 消息推送方法
     * @param array $data 消息对象
     * @param string $device 接收消息的设备类型,all:全部，android:指定安卓设备,ios:指定iOS设备 默认全部
     * @return arrau $rt 返回ios与android的各自发送状态
     */
//    function send($data, $device = 'all') {
//        $rt = array();
//        if (in_array($device, array('all', 'android'))) {
//            $rt['android'] = $this->send_android($data);
//        }
//        if (in_array($device, array('all', 'ios'))) {
//            $rt['ios'] = $this->send_ios($data);
//        }
//        return $rt;
//    }
//    private function _send() {
//        implode('Org.Net.Curl');
//        $curl = new \Org\Net\Curl();
//        return $curl->post($this->postUrl, $this->postBody);
//    }
//
//    function send_android($data) {
//        $this->appMasterSecret = $this->config['android']['app_master_secret'];
//        $this->_parseAndroidData($data);
//        $this->_sign();
//        return $this->_send();
//    }
//
//    function send_ios($data) {
//        $this->appMasterSecret = $this->config['ios']['app_master_secret'];
//        $this->_parseIosData($data);
//        $this->_sign();
//        return $this->_send();
//    }
//
//    private function _sign() {
//        $http_method = 'POST';
//        $sign = md5($http_method . $this->postUrl . $this->postBody . $this->appMasterSecret);
//        //dump($this->postBody);
//        $this->postUrl .= '?sign=' . $sign;
//    }
//
//    private function _parseAndroidData($data) {
//        $paras = array(
//            'appkey' => $this->config['android']['AppKey'],
//            'timestamp' => NOW_TIME,
//            'device_tokens' => 'AnsuxMIRQHYYReC0OE2n2NNq4v5bVtrWGimp1H6H8EkI',
//            'type' => 'unicast',
//            'payload' => array(
//                'display_type' => 'message',
//                'body' => array(
//                    'custom' => 'niaoting测试内容',
//                )
//            ),
//            'policy' => array(
//                'expire_time' => date('Y-m-d h:i:s', NOW_TIME + 86400)
//            ),
//            'production_mode' => 'false',
//            'description' => '测试单播消息-Android'
//        );
//        $this->postBody = json_encode($paras);
//    }
//
//    private function _parseIosData($data) {
//        $paras = array(
//            'appkey' => $this->config['ios']['AppKey'],
//            'timestamp' => NOW_TIME,
//            'device_tokens',
//            'type',
//            'payload' => $data
//        );
//        $this->postBody = json_encode($paras);
//    }
//    function __construct($key, $secret) {
//        $this->appkey = $key;
//        $this->appMasterSecret = $secret;
//        $this->timestamp = strval(time());
//    }

    /*function sendAndroidBroadcast($title,$ticker,$text,$go,$go_param) {
        try {
            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker", $ticker);
            $brocast->setPredefinedKeyValue("title", $title);
            $brocast->setPredefinedKeyValue("text", "Android broadcast text");
            $brocast->setPredefinedKeyValue("after_open", "go_app");
            // Set 'production_mode' to 'false' if it's a test device. 
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", "true");
            // [optional]Set extra fields
            $brocast->setExtraField("test", "helloworld");
            print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }*/

    /**
     * 
     * @param type $tokens 接收通知的用户设备token
     * @param type $ticker 通知栏提示文字
     * @param type $title 通知标题
     * @param type $text 通知文字描述 
     * @param type $after_open
     * @return type
     */
//    function sendAndroidUnicast($tokens, $ticker, $title, $text = '', $after_open = 'go_app') {
//        vendor('Umeng.android.AndroidUnicast');
//        try {
//            $unicast = new \AndroidUnicast();
//            $unicast->setAppMasterSecret($this->config['android']['app_master_secret']);
//            $unicast->setPredefinedKeyValue('appkey', $this->config['android']['app_key']);
//            $unicast->setPredefinedKeyValue('timestamp', NOW_TIME);
//            $unicast->setPredefinedKeyValue('device_tokens', $tokens);
//            $unicast->setPredefinedKeyValue('ticker', $ticker);
//            $unicast->setPredefinedKeyValue('title', $title);
//            $unicast->setPredefinedKeyValue('text', $text);
//            $unicast->setPredefinedKeyValue('after_open', $after_open);
//            $unicast->setPredefinedKeyValue("production_mode", $this->productionMode);
//            // Set extra fields
//            //$unicast->setExtraField("test", "helloworld");
//            return $unicast->send();
//        } catch (Exception $e) {
//            return array('error' => $e->getMessage());
//        }
//    }

//    function sendAndroidGroupcast() {
//        try {
//            /*
//             *  Construct the filter condition:
//             *  "where": 
//             * 	{
//             * 		"and": 
//             * 		[
//             * 			{"tag":"test"},
//             * 			{"tag":"Test"}
//             * 		]
//             * 	}
//             */
//            $filter = array(
//                "where" => array(
//                    "and" => array(
//                        array(
//                            "tag" => "test"
//                        ),
//                        array(
//                            "tag" => "Test"
//                        )
//                    )
//                )
//            );
//
//            $groupcast = new AndroidGroupcast();
//            $groupcast->setAppMasterSecret($this->appMasterSecret);
//            $groupcast->setPredefinedKeyValue("appkey", $this->appkey);
//            $groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
//            // Set the filter condition
//            $groupcast->setPredefinedKeyValue("filter", $filter);
//            $groupcast->setPredefinedKeyValue("ticker", "Android groupcast ticker");
//            $groupcast->setPredefinedKeyValue("title", "Android groupcast title");
//            $groupcast->setPredefinedKeyValue("text", "Android groupcast text");
//            $groupcast->setPredefinedKeyValue("after_open", "go_app");
//            // Set 'production_mode' to 'false' if it's a test device. 
//            // For how to register a test device, please see the developer doc.
//            $groupcast->setPredefinedKeyValue("production_mode", "true");
//            print("Sending groupcast notification, please wait...\r\n");
//            $groupcast->send();
//            print("Sent SUCCESS\r\n");
//        } catch (Exception $e) {
//            print("Caught exception: " . $e->getMessage());
//        }
//    }
//
//    function sendAndroidCustomizedcast($tokens = '') {
//        $tokenStr = is_array($tokens) ? implode(',', $tokens) : $tokens;
//        vendor('Umeng.android.AndroidCustomizedcast');
//        try {
//            $customizedcast = new \AndroidCustomizedcast();
//
//            $customizedcast->setAppMasterSecret($this->config['android']['app_master_secret']);
//            $customizedcast->setPredefinedKeyValue('appkey', $this->config['android']['app_key']);
//            $customizedcast->setPredefinedKeyValue('timestamp', NOW_TIME);
//
//            $customizedcast->setPredefinedKeyValue("alias", "25340");
//            // Set your alias_type here
//            $customizedcast->setPredefinedKeyValue("alias_type", "NIAOTING");
//            $customizedcast->setPredefinedKeyValue("ticker", "ticker --UID alias测试消息通知");
//            $customizedcast->setPredefinedKeyValue("title", "title --UID alias测试消息通知");
//            $customizedcast->setPredefinedKeyValue("text", "text --UID alias测试消息通知");
//            $customizedcast->setPredefinedKeyValue("after_open", "go_app");
//
//            print("Sending customizedcast notification, please wait...\r\n");
//            $rt = $customizedcast->send();
//            print("Sent SUCCESS\r\n");
//            return $rt;
//        } catch (Exception $e) {
//            print("Caught exception: " . $e->getMessage());
//        }
//    }
//
//    function sendIOSBroadcast($alert) {
//        try {
//            $brocast = new IOSBroadcast();
//            $brocast->setAppMasterSecret($this->appMasterSecret);
//            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
//            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
//
//            $brocast->setPredefinedKeyValue("alert", "IOS 广播测试");
//            $brocast->setPredefinedKeyValue("badge", 0);
//            $brocast->setPredefinedKeyValue("sound", "chime");
//            // Set 'production_mode' to 'true' if your app is under production mode
//            $brocast->setPredefinedKeyValue("production_mode", $this->productionMode);
//            // Set customized fields
//            $brocast->setCustomizedField("test", "helloworld");
//            return $brocast->send();
//        } catch (Exception $e) {
//            return array('error' => $e->getMessage());
//        }
//    }
//
//    function sendIOSUnicast() {
//        vendor('Umeng.ios.IOSUnicast');
//        try {
//            $unicast = new \IOSUnicast();
//            $unicast->setAppMasterSecret($this->config['ios']['app_master_secret']);
//            $unicast->setPredefinedKeyValue("appkey", $this->config['ios']['app_key']);
//            $unicast->setPredefinedKeyValue("timestamp", NOW_TIME);
//            // Set your device tokens here
//            $unicast->setPredefinedKeyValue("device_tokens", "xx");
//            $unicast->setPredefinedKeyValue("alert", "IOS 单播测试");
//            $unicast->setPredefinedKeyValue("badge", 0);
//            $unicast->setPredefinedKeyValue("sound", "chime");
//            // Set 'production_mode' to 'true' if your app is under production mode
//            $unicast->setPredefinedKeyValue("production_mode", "false");
//            // Set customized fields
//            $unicast->setCustomizedField("test", "helloworld");
//            print("Sending unicast notification, please wait...\r\n");
//            $unicast->send();
//            print("Sent SUCCESS\r\n");
//        } catch (Exception $e) {
//            print("Caught exception: " . $e->getMessage());
//        }
//    }
//
//    function sendIOSGroupcast() {
//        try {
//            /*
//             *  Construct the filter condition:
//             *  "where": 
//             * 	{
//             * 		"and": 
//             * 		[
//             * 			{"tag":"iostest"}
//             * 		]
//             * 	}
//             */
//            $filter = array(
//                "where" => array(
//                    "and" => array(
//                        array(
//                            "tag" => "iostest"
//                        )
//                    )
//                )
//            );
//
//            $groupcast = new IOSGroupcast();
//            $groupcast->setAppMasterSecret($this->appMasterSecret);
//            $groupcast->setPredefinedKeyValue("appkey", $this->appkey);
//            $groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
//            // Set the filter condition
//            $groupcast->setPredefinedKeyValue("filter", $filter);
//            $groupcast->setPredefinedKeyValue("alert", "IOS 组播测试");
//            $groupcast->setPredefinedKeyValue("badge", 0);
//            $groupcast->setPredefinedKeyValue("sound", "chime");
//            // Set 'production_mode' to 'true' if your app is under production mode
//            $groupcast->setPredefinedKeyValue("production_mode", "false");
//            print("Sending groupcast notification, please wait...\r\n");
//            $groupcast->send();
//            print("Sent SUCCESS\r\n");
//        } catch (Exception $e) {
//            print("Caught exception: " . $e->getMessage());
//        }
//    }
//
//    function sendIOSCustomizedcast() {
//        try {
//            $customizedcast = new IOSCustomizedcast();
//            $customizedcast->setAppMasterSecret($this->appMasterSecret);
//            $customizedcast->setPredefinedKeyValue("appkey", $this->appkey);
//            $customizedcast->setPredefinedKeyValue("timestamp", $this->timestamp);
//
//            // Set your alias here, and use comma to split them if there are multiple alias.
//            // And if you have many alias, you can also upload a file containing these alias, then 
//            // use file_id to send customized notification.
//            $customizedcast->setPredefinedKeyValue("alias", "xx");
//            // Set your alias_type here
//            $customizedcast->setPredefinedKeyValue("alias_type", "xx");
//            $customizedcast->setPredefinedKeyValue("alert", "IOS 个性化测试");
//            $customizedcast->setPredefinedKeyValue("badge", 0);
//            $customizedcast->setPredefinedKeyValue("sound", "chime");
//            // Set 'production_mode' to 'true' if your app is under production mode
//            $customizedcast->setPredefinedKeyValue("production_mode", "false");
//            print("Sending customizedcast notification, please wait...\r\n");
//            $customizedcast->send();
//            print("Sent SUCCESS\r\n");
//        } catch (Exception $e) {
//            print("Caught exception: " . $e->getMessage());
//        }
//    }

    /**
     * 对用户UID加密后返回给alias
     * @param int $uid
     * @return string 
     */
    private function _getHashUID($uid){
        $salt = $this->config['salt'];
        return md5($uid.$salt);
    }
}
