<?php

namespace Common\Controller;

use Think\Controller\RestController;

/**
 * Description of RestfullController
 *
 * @author Kevin
 */
class RestfulController extends RestController {

    public $return;
    public $body;
	public $token;

    function _initialize() {
        
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');
        
        if (defined('UID')) {
            return false;
        }
        //初始化用户数据
        $this->getTokenValidation();
        
        //初始化返回数据
        $this->return = array(
            'code' => '200',
            'message' => 'success',
            'data' => array()
        );
        
        //全局app版本号，客户端类型
        define('CLIENT'  , I('get.client'));
        define('VERSION' , I('get.version'));
        define('CLIENTTIME' , I('get.time'));  //客户端时间
        define('APPID'   , I('get.appid' ,1));

        $this->getRawBody();
        
        $body = $this->body;
        if($body){
            $headimg = '';
            if($body['action'] == 'update'){
                $headimg = $body['headimg'];
            }
            foreach ($body as $key => $value) {
                $val = urldecode($value);
                $body[$key] = $val;
            }
            if($headimg){
                $body['headimg'] = $headimg;
            }
            $this->body = $body;
        }
        //用户访问记录
        $util = D('Common/Util' , 'Logic');
        $util->userVisitLog($this->body['action']);
        
//         if(in_array($this->body['action'], C('ALLOW_ACTION'))) {
//             if(I('get.isdebug') <> 9999) {
//                 $header = $this->getHeaders('' , 'Sign');
//                 $ret = $util->validateSign($header);
//                 if(-1 == $ret){
//                     $this->return['code'] = 307;
//                     return $this->responseJson();
//                 }elseif(-2 == $ret) {
//                     $this->return['code'] = 306;
//                     return $this->responseJson();
//                 }elseif(-3 == $ret){
//                     $this->return['code'] = 308;
//                     return $this->responseJson();
//                 }
//             }
//         }
    }
    
    public function __call($method, $args) {
        //parent::__call($method, $args);
    }

        /**
     * 路由控制，检测 action 是否有效
     */
    protected function roleController() {
        
        $action = $this->postdata['action'];
        //验证合法的action
        if (!in_array($action, $this->actions)) {
            $this->return['code'] = 301;
            return $this->responseJson();
        }
        return $this->$action();
        
    }
    

    /**
     * 获取流文件内容
     * @return mixed|boolean
     */
    protected function getRawBody() {
        if($this->body)
            return $this->body;
        
        $this->body = file_get_contents('php://input');
        $bady_data = $this->body;
        if (strlen(trim($this->body)) == 0 && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $this->body = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        if (strlen(trim($this->body)) > 0) {
            $this->body = json_decode($this->body, true);
            if($this->body){
                return $this->body;
            }else{
                $body_arr = explode('&', $bady_data);
                $new_arr = array();
                foreach ($body_arr as $key => $value) {
                    $n_a = explode('=', $value);
                    if($n_a){
                        
                        for ($i = 0; $i < count($n_a); $i++) {
                            $str_arr = array('"', '}', '{', "\r", "\n", ' ');
                            $n_a[$i] = str_replace($str_arr, '', $n_a[$i]);
                        }
                        $new_arr[$n_a[0]] = $n_a[1];
                    }
                }
                $this->body = $new_arr;
                return $this->body;
            }
            
        }
        return false;
    }
    
    /**
     * 根据TOKEN获取UID
     * @return number 返回用户的uid
     */
    protected function getTokenValidation() {
        $token = $this->token = $this->getHeaders('Token');
        
        if ($token && strlen($token)==32) {
            
            //Token用户己经登录，根据token获取用户uid
            $member = D('Member/MemberToken');
            $uid = $member->getUidByToken($token);
            define('UID' , $uid);
            
            return ($uid) ? $uid : 0;
        } else {
            define('UID' , 0);
            return 0;
        }
    }
    
    /**
     * 获取header传的参数
     * @param $param header中的名字
     * @param $field header中的字段名字,字段名字与值是空格分隔,类似 Token 3333333.
     * @return headers Array
     */
    protected function getHeaders($field = '' , $param = 'Authorization') {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');
        $headers = getallheaders();
        
        //如果没有指定返回具体的字段，则直接返回header信息
        if(!$field) {
            return $headers[$param];
        } else {
            $info = explode(' ', $headers[$param]);
			if(!$info[1] || $info[1] == 'null')
				$info[1] = '';

            $res[$info[0]] = $info[1];
            
            return $res[$field];
        }
    }

    /**
     * 输出JSON
     * @param type $data
     */
    protected function responseJson($data = false, $message = '') {
        if (!$data) {//未指定输出值 时，取输出message
            $codes = C('RETURN_CODES');
            $this->return['message'] = empty($codes[$this->return['code']]) ? $message : $codes[$this->return['code']];
        }
        
        if(!$this->return['data'])
            $this->return['data'] = array(); //如果data没有的话置为 array(),防止输出null
        $this->return = str_replace('""""','""',str_replace("null" , '""' , json_encode($this->return)));
        $this->sendHttpStatus($this->httpStatusCode);
        $this->setContentType('json');
        exit($this->return);
        
         //$this->response($data ? $data : $this->return, 'json', $this->httpStatusCode);
    }
    
    /**
     * 判断用户是否己登录
     */
    protected function checkLogin() {
        
        if(!intval(UID)) {
            //用户未登录
            $this->return['code'] = 437;
            return $this->responseJson();
        }
    }

}
