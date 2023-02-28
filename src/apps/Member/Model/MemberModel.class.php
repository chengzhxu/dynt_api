<?php

namespace Member\Model;

use Think\Model;

/**
 * 用户基本信息类
 *
 * @author Kevin
 */
class MemberModel extends Model {

    /**
     * 获取用户的基本信息
     * 从缓存中取出，如果没有再从数据库中查询，再写入缓存
     * @param $username 用户名/手机或UID
     * @param $type 区别是根据用户名获取或UID获取
     * @param $type=1 用户名/手机，$type=2 UID获取
     * @param $appid 应用ID，默认为1
     */
    function getUserInfo($username = '' , $type = 1) {
        $userinfo = array();
        
        $key = get_cache_key($username , $type);
        $userinfo = S($key);
        
        if(!$userinfo){
            if($type == 1){        //用户名获取用户信息
                $userinfo = M('member_account')->where(array('mobile' => $username))->find();
            }else if($type == 2){       //uid获取用户信息
                $userinfo = M('member_account')->where(array('uid' => $username))->find();
            }
        }
        if($userinfo){
            S(get_cache_key($username , $type) , $userinfo);
        }
        return $userinfo;
    }
    
    
}
