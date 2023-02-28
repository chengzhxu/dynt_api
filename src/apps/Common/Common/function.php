<?php

/**
 * @author Kevin
 */
/**
 * 只有PHP以apache服务器的模块(module)方式执行时 getallheaders这个系统自带方法
 * 如果是nginx的话是不存在 getallheaders 这个方法的
 * 这个是解决除apache以外的服务器来获取所有 HTTP 变量值，
 */
if (!function_exists('getallheaders')) {

    function getallheaders() {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

}

//判断微信内置浏览器
function is_weixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * 验证手机号合法性
 * @param type $mobile
 * @return boolean
 */
function validate_mobile($mobile) {
    $search = '/1[0-9]{1}\d{9}$/';
    if (preg_match($search, $mobile)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 
 * @param number $type
 * @param $type=1 用户名/手机，$type=2 UID获取
 */
function get_cache_key($username, $type = 1) {

    $config = C('ALLOW_OTHER_APP_LOGIN');
    $appid = APPID;

    if ($type == 1) {
        if (!$config)
            $key = 'member' . $username . $appid; //一个帐号不可以登录所有的APP,key值就是手机号+appid
        else
            $key = 'member' . $username;  //一个帐号可以登录所有的APP
    } else {
        $key = 'uid' . $username;
    }

    return $key;
}

/**
 * 生成随机数
 * @param unknown $length
 * @return Ambigous <NULL, string>
 */
function createRandChar($length = 11) {
    $str = null;
    $strPol = "23456789abcdefghjklmnpqrstuvwxyz";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str.=$strPol[mt_rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

/**
 * 清除数组中的空数据
 * @param unknown $data
 * @return multitype:unknown
 */
function delete_empty_array($data) {

    $newdata = array();
    if ($data && is_array($data)) {
        foreach ($data as $key => $value) {
            if (trim($value)) {
                $newdata[$key] = $value;
            }
        }
    }
    return $newdata;
}

/**
 * 获取用户信息方法
 * 公用方法，方便直接调用
 * $username 用户名或手机
 * $type是根据用户名获取还是UID获取，type=1 用户名  type=2UID
 */
function getUserInfo($username, $type = 1) {

    return D('Member/Member')->getUserInfo($username, $type);
}

/**
 * 返回默认头像
 */
function getDefaultHeadimg(){
    return '';
}


/**
 * 评论递归
 * @param intval $id			评论ID
 */
 function getReply($id){
	//查看是否有回复
	$reply = M('common_comment')->field($field)
								->where(array('deleted' => 0 ,'parent_id' => $id))
								->order('id asc')->select();
	$newreply = array();
	if($reply) {
		foreach($reply as $v) {
			$userinfo = getUserInfo($v['from_uid'] , 2);
			
			$v['from_headimg'] = !empty($userinfo['headimg']) ? $userinfo['headimg'] : getDefaultHeadimg();
			$v['from_nick']= !empty($userinfo['nickname']) ? $userinfo['nickname'] : '路人甲';
			
			$u = getUserInfo($v['to_uid'] , 2);
			$v['to_headimg'] = !empty($u['headimg']) ? $u['headimg'] : getDefaultHeadimg();
			$v['to_nick'] = !empty($u['nickname']) ? $u['nickname'] : '路人丙';
			$newreply= $v;
			$newreply['reply'] = getReply($v['id']);
			//$arrs[] = $newreply['reply'];
            $arr[] = $newreply;  
		}
		return $arr;
	}else{
		return array();
	}
	
 }
 
 
 function arraySingle($array,$isTrue){
	if($array[0]['id']){
		static $result_array;
		if($isTrue){
			$result_array = array();
		}
		foreach($array as $key => $value){
			if($value['reply']){
				$result_array[]=array(
					'id'=>$value['id'],
					'from_uid'=>$value['from_uid'],
					'to_uid'=>$value['to_uid'],
					'objtype'=>$value['objtype'],
					'parent_id'=>$value['parent_id'],
					'content'=>$value['content'],
					'dateline'=>$value['dateline'],
					'deleted'=>$value['deleted'],
					'from_headimg'=>$value['from_headimg'],
					'from_nick'=>$value['from_nick'],
					'to_headimg'=>$value['to_headimg'],
					'to_nick'=>$value['to_nick'],
					'reply'=>array(),
				);
				
				arraySingle($value['reply'],0);
			}else{
				
				$result_array[]=array(
					'id'=>$value['id'],
					'from_uid'=>$value['from_uid'],
					'to_uid'=>$value['to_uid'],
					'objtype'=>$value['objtype'],
					'parent_id'=>$value['parent_id'],
					'content'=>$value['content'],
					'dateline'=>$value['dateline'],
					'deleted'=>$value['deleted'],
					'from_headimg'=>$value['from_headimg'],
					'from_nick'=>$value['from_nick'],
					'to_headimg'=>$value['to_headimg'],
					'to_nick'=>$value['to_nick'],
					'reply'=>array(),
				);
				
				
				
			}
			
		}
		return $result_array;
	}else{
		return array();
	}
	
	
	
}


/**
 * 获取对象表名
 */
function getTable($objtype){
    $table = '';
    switch ($objtype) {
        case 1:        //话题
            $table = 'common_topic';
            break;
        case 2:        //招聘
            $table = 'common_recruit';
            break;
        case 3:        //求职
            $table = 'common_job';
            break;
    }
    return $table;
}


/**
 * 返回用户年龄
 * unixtime
 */
function getUserAge($birthday) {
    $birthday = (is_string($birthday)) ? strtotime($birthday) : $birthday;
    if ($birthday > 0) {
        $year = date('Y', $birthday);
        if (($month = (date('m') - date('m', $birthday))) < 0) {
            $year++;
        } else if ($month == 0 && date('d') - date('d', $birthday) < 0) {
            $year++;
        }
        return date('Y') - $year;
    } else
        return 0;  //默认25岁
}
/**
 * 根据UID返回用户真实年龄
 * @param int $uid
 * @return int $age
 */
function getUserAgeByUid($uid) {
    $age = S('M_AGE_' . $uid);
    if (!$age) {
        $user = getUserInfo($uid, 2);
        $age = getUserAge($user['profile']['birthday']);
        S('M_AGE_' . $uid, $age);
    }
    return $age;
}

/**
 * 根据等级转换成积分
 * @param unknown $level
 */
function getCredit($level) {

    if ($level == 0)
        $credit = 0;
    elseif ($level == 1)
        $credit = 50;
    elseif ($level == 2)
        $credit = 200;
    elseif ($level == 3)
        $credit = 500;
    elseif ($level == 4)
        $credit = 1000;
    elseif ($level == 5)
        $credit = 2000;
    elseif ($level == 6)
        $credit = 5000;
    elseif ($level == 7)
        $credit = 10000;
    elseif ($level == 8)
        $credit = 20000;
    elseif ($level == 9)
        $credit = 50000;
    else
        $credit = 60000;

    return $credit;
}

/**
 * 根据积分转换成等级
 * @param unknown $credit
 */
function getLevel($credit) {
	
	if($credit<50){
		$level == 0;
	}elseif($credit>=50 && $credit<200){
		$level = 1;
	}elseif($credit>=200 && $credit<500){
		$level = 2;
	}elseif($credit>=500 && $credit<1000){
		$level = 3;
	}elseif($credit>=1000 && $credit<2000){
		$level = 4;
	}elseif($credit>=2000 && $credit<5000){
		$level = 5;
	}elseif($credit>=5000 && $credit<10000){
		$level = 6;
	}elseif($credit>=10000 && $credit<20000){
		$level = 7;
	}elseif($credit>=20000 && $credit<50000){
		$level = 8;
	}elseif($credit>=50000 && $credit<60000){
		$level = 9;
	}else{
		$level = 10;
	}
	return $level;
}


/**
 * 快捷日志输出方法，以支持对象的输出
 * @param fixed $data
 * @author Floy
 */
function dlog($data) {
    $str = is_string($data) ? $data : print_r($data, 1);
    \Think\Log::write($str, 'INFO');
}

/**
*	中文截取
**/
function msubstr($string, $length, $dot = '...')
		{
			$char = "utf-8";
			$strlen = strlen($string);
			if($strlen <= $length) return $string;
			$string = str_replace(array('&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;'), array(' ', '&', '"', "'", '“', '”', '—', '<', '>'), $string);
			$strcut = '';
			if($char == 'utf-8')
			{
				$n = $tn = $noc = 0;
				while($n < $strlen)
				{
					$t = ord($string[$n]);
					if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
						$tn = 1; $n++; $noc++;
					} elseif(194 <= $t && $t <= 223) {
						$tn = 2; $n += 2; $noc += 2;
					} elseif(224 <= $t && $t < 239) {
						$tn = 3; $n += 3; $noc += 2;
					} elseif(240 <= $t && $t <= 247) {
						$tn = 4; $n += 4; $noc += 2;
					} elseif(248 <= $t && $t <= 251) {
						$tn = 5; $n += 5; $noc += 2;
					} elseif($t == 252 || $t == 253) {
						$tn = 6; $n += 6; $noc += 2;
					} else {
						$n++;
					}
					if($noc >= $length) break;
				}
				if($noc > $length) $n -= $tn;
				$strcut = substr($string, 0, $n);
			}
			else
			{
				$dotlen = strlen($dot);
				$maxi = $length - $dotlen - 1;
				for($i = 0; $i < $maxi; $i++)
				{
					$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
				}
			}
			$strcut = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'), $strcut);
		
			$length = $length / 2 * 3;
			if($strlen > $length){
				$strcu = $strcut.$dot;
			}else {
				$strcu = $strcut;
			}
			return $strcu;
		}


/**
 * 生产订单号
 * @param intval $uid          用户ID
 * @param intval $goodsCount   商品数量
 * @param intval $from    订单来源(金币商城:J;其他:F)
 */
function orderNum($uid,$goodsCount,$from){
	$userinfo = getUserInfo($uid,2);
	$ymd = date('ymd',NOW_TIME);
	$hi = date('hi',NOW_TIME);
	$orderCount = '01';
	$goodsCount = $goodsCount >= 10 ? $goodsCount:'0'.$goodsCount;
	$level = getLevel($userinfo['profile']['credit2']) >=10 ? getLevel($userinfo['profile']['credit2']):'0'.getLevel($userinfo['profile']['credit2']);
	$uniqid = strtoupper(substr(uniqid(),-6));
	$from = 'F';
	
	return $from.$goodsCount.$level.$uniqid.$ymd.$orderCount.$hi;
}

/**
 * 根据后台配置的枚举类型，分割成数组
 * @param unknown $data
 * @param $returnvalue -1的话表示返回一个数据，其它值表示取这个值对应的文字
 */
function get_select($name = '' , $returnvalue = -1) {
    
    $return = array();
    $data = D('SysConfig')->where(array('status' => 1, 'name' => $name))->field('id,name,title,extra,value,remark,type')->order('sort')->find();
    
    if($data) {
        if($data['extra']) {
            $res = explode("\n" , $data['extra']);
            foreach($res as $value) {
                $datas = explode(':' , $value);
                
                if($returnvalue > -1) {
                    if($datas[0] == $returnvalue)
                        return $datas[1];
                }
                $return[$datas[0]] = trim($datas[1]);
            }
        }
    }
    
    return $return;
}

/**
 * 设置红包已使用、更新用户零钱、记录零钱日志
 * @param intval   $packet_id  红包ID
 * @param intval   $uid		   用户ID
 * @param floatval $smallMoney 零钱  
 */
function setPacketMoney($packet_id,$uid,$smallMoney){
// 	if($smallMoney>0){
// 		$credit = M('MemberCredits')->where( array('uid'=>$uid) )->find();//零钱
// 		$credit3 = $credit['credit3'];//可提现零钱
// 		$credit4 = $credit['credit4'];//不可提现零钱
// 		if($smallMoney <= $credit3){
// 			$credit3 = $smallMoney;
// 			$credit4 = 0;
// 		}else{
// 			$credit4 = $smallMoney - $credit3;
// 		}
// 		if($credit3){
// 			//更新用户可提现零钱
// 			M('MemberCredits')->where( array('uid'=>$uid) )->setDec('credit3',$credit3);
// 			//记录用户可提现零钱日志
// 			M('MoneyLogs')->add( array('uid'=>$uid,'money'=>-$credit3,'dateline'=>NOW_TIME) );
// 		}
// 		if($credit4){
// 			//更新用户不可提现零钱
// 			M('MemberCredits')->where( array('uid'=>$uid) )->setDec('credit4',$credit4);
// 			//记录用户不可提现零钱日志
// 			M('MoneyLogs')->add( array('uid'=>$uid,'money'=>-$credit4,'dateline'=>NOW_TIME) );
// 		}
// 	}
	
	//设置红包已使用
	if($packet_id){
		M('RedPackets')->where( array('id'=>$packet_id) )->save( array('is_used'=>1,'use_time'=>NOW_TIME) );
	}
}