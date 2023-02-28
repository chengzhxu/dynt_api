<?php

namespace Member\Model;

use Think\Model;

/**
 * 用户登录时的 token类
 *
 * @author Kevin
 */
class MemberTokenModel extends Model {
    
    /**
     * 登录时创建用户的token值
     * 
     * @param unknown $uid  用户UID
     * 
     * @return 返回md5产生的token的值
     */
    function createToken($uid) {
        
        //查询该UID是否有产生token值
        $where['uid'] = $uid;
        $result = $this->where($where)->find();
        
        //创建
        if(!$result) {
            
            $token = md5($uid.NOW_TIME);
            $data['uid']      = $uid;
            $data['token']    = $token;
            $data['dateline'] = NOW_TIME;
             
            $this->add($data);
            
            //写入缓存
            S($token , $uid);  //token对应uid
        } else {
            $token = $result['token'];
        }
        
        return $token;
    }
    
}
