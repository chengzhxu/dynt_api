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
     * 根据token返回用户 UID
     * 先用 S方法 从缓存中取出，如果没有的话，从数据库中查询，查出来后记录进缓存
     * @return int uid
     */
    function getUidByToken($token) {
        
        $uid = S($token);
        
        //如果不存在，则从数据库中查询，再记录进缓存
        if(!$uid) {
            
            $where['token'] = $token;
            $res = $this->where($where)->find();
            if($res) {
                //记录缓存
                S($token , $res['uid']);
                $uid = $res['uid'];
            } else {
                $uid = 0;
            }
        }
        return $uid;
    }
    
    /**
     * 根据uid获取token
     */
    function getTokenByuid($uid){
        //查询该UID是否有产生token值
        $where['uid'] = $uid;
        $result = $this->where($where)->find();
        if($result){
            return true;
        }
    }
    
    
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
    
    /**
     * 删除用户己产生的token
     * @param $token 删除指定用户的token
     * 先删除缓存后，再删除数据库记录
     */
    function deleteToken($token) {
        
        //清除缓存
        S($token , null);
        
        $where['token'] = $token;
        $this->where($where)->delete();
        
        return true;
    }

}
