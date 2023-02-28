<?php

namespace Member\Logic;

/**
 * Description of MemberLogic
 *
 * @author Kevin
 */

class MemberLogic {

    private $member;
    function __construct() {
        
        $this->member = D('Member');
    }
    
    /**
     * 用户登录
     * base64_decode 后的结果应该是 111111:111111:0 这样结构的数据
     * 分号隔开，第一个是用户名，第二个是密码，第三个是appid
     */
    function login($users) {
		if($users) {
			$uinfo = explode(':' , $users);
			if($uinfo && is_array($uinfo)) {
				$userinfo = $this->member->getUserInfo($uinfo[0]);
				if($userinfo) {
					
					//验证密码是否正确
					if(md5($uinfo[1].$userinfo['account']['salt']) == $userinfo['account']['password']) {
						//密码验证成功
						if($userinfo['member']['status'] == 0) {
							//用户锁定
							$return['code'] = 432;
						} else {
							
							//生成Token
							$mtoken = D('MemberToken');
							$token = $mtoken->createToken($userinfo['account']['uid']);

							//用户积分
							$credit = M('member_credits')->where(array('uid' => $userinfo['account']['uid']))->find();
							//登录时间
							M('MemberProfile')->where( array( 'uid'=>$userinfo['account']['uid']) )->setField('last_time',NOW_TIME);
							//验证成功，返回用户信息
							$return['code'] = 200;
							$return['message'] = '';
							if( $userinfo['profile']['groupid'] == 1 ){
								$credit = M('MemberCredits')->where( array('uid'=>$userinfo['account']['uid']) )->getField('credit2');
								if( $credit >= C('MASTER_CREDIT') ){
									$userinfo['profile']['groupid'] = 99;
								}

							}
							session('uid',$userinfo['account']['uid']);
							D('Cart/CartWeb')->cartCount(0);
							$return['data'] = array(
								'id'            => $userinfo['account']['uid'],
								'groupid'       => $userinfo['profile']['groupid'],
								'token'         => $token,
								'headimg'		=> $userinfo['profile']['headimg'],
								'username'		=> $userinfo['account']['username'],
								'email'			=> $userinfo['account']['email'],
								'mobile'		=> $userinfo['member']['mobile'],
								'nickname'		=> $userinfo['profile']['nickname'],
								'gender'		=> $userinfo['profile']['gender'],
								'birthday'		=> $userinfo['profile']['birthday'],
								'region'		=> $userinfo['profile']['regions'],
								'full_district' => $userinfo['regions']['full_district'],
								'skin'		    => $userinfo['profile']['skin'],
								'credit1'		=> $credit['credit1'],
								'credit2'       => $credit['credit2'],
								'status'        => $userinfo['member']['status'],
								'invitecode'    => $userinfo['profile']['invitecode'],
								'login_type'    => 'mobile',
								'is_lock'       => $userinfo['account']['is_lock'],
							);
							if( $userinfo['profile']['groupid'] == 5 || $userinfo['profile']['groupid'] == 6){
								$return['data']['promotions'] = M('Merchant')->where( array('uid'=>$result['uid']) )->getField('promotions');
							}
							
						}
					} else {
						//密码错误
						$return['code'] = 433;
					}
				} else {
					$return['code'] = 434;
				}
			} else {
				$return['code'] = 302;
			}
		} else {
			$return['code'] = 302;
		}
        return $return;
    }
    
    
    /**
     * 用户注册
     * @param $mobile 用户注册手机号
     * @param $username 注册用户名
     * @param $data     注册其它信息如密码等
     * @return 返回注册成功后的UID
     */
    function register($mobile, $username, $data = array()) {
        
        if (!$mobile || !$username) {
            return -1; //用户名或手机号为空
        }
        
        if ($this->check_exist($mobile)) {
			$uid = M('MemberAccount')->where( array('username'=>$mobile) )->getField('uid');
            session('uid',$uid);
			D('Cart/CartWeb')->cartCount(0);
			return $uid;
        }
        //初始化uid
        $uid = 0;
        
        if($data['type'] == 'third') {
            $rootid = 0;
        } else {
            $rootdata = array('mobile' => $mobile , 'status' => 1);
            $rootid = M('Member')->add($rootdata);
        }
        
        if($rootid  || $data['type'] == 'third') {
            
            $salt = rand(100000, 999999); //随机码
            
            if($data['source']) {
                if($data['source'] == 'qq')
                    $source = 1;
                elseif($data['source'] == 'weixin')
                    $source = 2;
                elseif($data['source'] == 'weibo')
                    $source = 3;
                else
                    $source = 0;
            } else 
                $source = 0;
            //写入其它从表
            $account = array(
                'rootid'   => $rootid,
                'username' => $username,
                'password' => md5($data['password'] . $salt),
                'salt'     => $salt,
                'appid'    => APPID,
                'email'    => isset($data['email']) ? $data['email'] : '',
                'type'     => $source,
            );
            
            $nickname = isset($data['nickname']) ? $data['nickname'] : '用户' . $salt;
            
            if($nickname) {
                //转换成拼音
                Vendor('Pinyin.Pinyin#class');
                $py = new \Pinyin();
                $index_name = $py->getFirstPY($nickname);
            }
            if($uid = M('member_account')->add($account)) {
                $profile = array(
                    'uid' => $uid,
                    'nickname' => $nickname,
                    'index_name'=>$index_name,
                    'birthday' => '',
                    'headimg'  => isset($data['headimg']) ? $data['headimg'] : '',
                    'gender'   => isset($data['gender']) ? $data['gender'] : 0,
                    'source'   => isset($data['source']) ? $data['source'] : 'sys',
                    'dateline' => NOW_TIME,
                    'last_time'=> NOW_TIME,
                    'regions'  => '',
                    'recommend'=> isset($data['recommend']) ? $data['recommend'] : '',
                    'invitecode'=> 163568 + $uid,     //加个固定数字，把邀请码位数定在6位以上
                    'reg_client'=> CLIENT,  //客户端时间
                );
				session('uid',$uid);
				D('Cart/CartWeb')->cartCount(0);
                M('member_profile')->add($profile);
                
                $credit = array(
                    'uid' => $uid
                );
                M('member_credits')->add($credit);
                
                //奖励积分,写入积分表
                $credit = D('Member/Credit' , 'Logic');
                $credit->log($uid,'register');
                
                
            }
        }
        
        return $uid;
        
    }
    
    
    
    /**
     * 手机号是否存在
     * @param unknown $mobile
     */
    function mobile_bind($mobile) {
        
        if (!$mobile)
            return false;
        
        $userinfo = getUserInfo($mobile);  //获取这个手机号是否有注册
        
        if(!$userinfo) {
            return true;
        } else {
            
            $curuser = getUserInfo(UID , 2);  //当前登录用户的信息
            $type = $curuser['account']['type'];
            
            $count = M('member_account')->where(array('rootid' => $userinfo['member']['id'] , 'type' => $type))->count();
            
            if($count > 0) //己绑定过
                return false;
            else 
                return true;  //未绑定，可以绑定
            
        }
        
    }

	/**
     * 查询用户是否存在
     * @param unknown $option 用户名或手机号
     * @param $type为手机号或ID
     * @return Array 返回用户信息
     */
    function check_exist($username , $type = 1) {
        if (!$username)
            return false;
        
        $return = getUserInfo($username , $type);
        return $return['profile']['uid'];
    }

	
}
