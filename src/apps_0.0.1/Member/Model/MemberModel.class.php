<?php

namespace Member\Model;

use Think\Model;

/**
 * 用户基本信息类
 *
 * @author Kevin
 */
class MemberModel extends Model {
    protected $autoCheckFields = false;
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
            if($userinfo['regions'] == 0){
                $address = '丹阳';
            }else{
                $address = get_select(WORK_ADDRESS,$userinfo['regions']) ? get_select(WORK_ADDRESS,$userinfo['regions']) : '丹阳';
            }
            
            if(!strstr($userinfo['headimg'], 'default_headimg')){
                if(!strstr($userinfo['headimg'], 'Resizerimg')){
                    $headimg_info = getimagesize($userinfo['headimg']);
                    $ratio = sprintf("%.2f", $headimg_info[0] / $headimg_info[1]);
                    $headimg_width = 250;
                    $headimg_height = round($headimg_width / $ratio);
                    $res_headimg = str_replace('http://','',$userinfo['headimg']);
                    $res_headimg = str_replace('/','_',$res_headimg);
                    $userinfo['headimg'] = 'http://api.danyangniaoting.com/util/image/Resizerimg/w/'.$headimg_width.'/h/'.$headimg_height.'/url/'.$res_headimg;
                }
            }
            
            $userinfo['nickname'] = ubb_to_emoij($userinfo['nickname']);
            $userinfo['address'] = $address;
            $userinfo['id'] = $userinfo['uid'];
            S(get_cache_key($username , $type) , $userinfo);
        }
        
        return $userinfo;
    }
    
    /**
     * 获取用户信息
     */
    function info($token , $data) {
        if(!$data['uid']){
            $userinfo = array();
        }else{
            $userinfo = getUserInfo($data['uid'] , 2);
            $userinfo['headimg'] = M('member_account')->where(array('uid' => $data['uid']))->getField('headimg');
        
            unset($userinfo['password']);
            unset($userinfo['salt']);
            unset($userinfo['type']);
            unset($userinfo['dateline']);
            unset($userinfo['last_time']);
            
            
//            if($userinfo['gender'] == 1){
//                $userinfo['gender'] = '男';
//            }else if($userinfo['gender'] == 0){
//                $userinfo['gender'] = '女';
//            }
            $favorite_count = M('common_favorite')->where(array('uid' => $data['uid']))->count();  //收藏数量
            $fans_count = M('sns_follow')->alias('s')->join('join dy_member_account a on a.uid = s.uid')->where(array('s.fid' => $data['uid']))->count();     //粉丝数量
            $follow_count = M('sns_follow')->alias('s')->join('join dy_member_account a on a.uid = s.fid')->where(array('s.uid' => $data['uid']))->count();   //关注数量

            $userinfo['favorite_count'] = $favorite_count;
            $userinfo['fans_count'] = $fans_count;
            $userinfo['follow_count'] = $follow_count;
            
            if($data['uid'] != UID){            //非自己主页
                $is_follow = 0;           //是否关注当前用户
                if(M('sns_follow')->where(array('uid' => UID, 'fid' => $data['uid']))->find()){
                    $is_follow = 1;
                }
                $userinfo['is_follow'] = $is_follow;
                
                $is_defriend = 0;    //是否拉黑当前用户
                $is_shield = 0;    //是否屏蔽当前用户
                $black_member = M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->find();
                if($black_member){
                    $is_defriend = intval($black_member['is_defriend']);
                    $is_shield = intval($black_member['is_shield']);
                }
                $userinfo['is_defriend'] = $is_defriend;
                $userinfo['is_shield'] = $is_shield;
            }
        }
        
        $return['code'] = 200;
        $return['message'] = '';
        $return['data'] = $userinfo;
        
        return $return;
    }
}
