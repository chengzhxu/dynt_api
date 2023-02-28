<?php

namespace Member\Logic;

/**
 * 用户相关
 *
 * @author Kevin
 */
class FriendLogic {
    
    /**
     * (取消)关注用户
     */
    function follow($data){
        if(!$data['fid']){
            $return['code'] = 434;
            $return['message'] = '';
            return $return;
        }
        if($data['fid'] == UID){
            $return['code'] = 501;
            $return['message'] = '';
            return $return;
        }
        
        if(!M('member_account')->where(array('uid' => $data['fid']))->find()){
            $return['code'] = 503;
            $return['message'] = '';
            return $return;
        }
        
        $is_muttual = 0;
        $rt_data = array();
        $is_follow = 0;
        $where['uid'] = $data['fid'];
        $where['fid'] = UID;
        if(M('sns_follow')->where($where)->find()){           //判断是否为双向关注
            $is_muttual = 1;
        }
        $snsmap['uid'] = UID;
        $snsmap['fid'] = $data['fid'];
        if(M('sns_follow')->where($snsmap)->find()){              //已关注 (取消)
            M('sns_follow')->where($snsmap)->delete();
            
            if($is_muttual){      //改为单向关注
                M('sns_follow')->where($where)->setField('muttual',0);
                $is_muttual = 0;
            }
            $rt_data['type'] = 'cancel_follow'; 
        }else{            //未关注  (加关注)
            $sns_arr = array(
                'uid' => UID,
                'fid' => $data['fid'],
                'muttual' => $is_muttual,
                'dateline' => NOW_TIME
            );
            if(M('sns_follow')->add($sns_arr)){       //关注成功
                //发送通知
                $rt_data['type'] = 'follow'; 
                $is_follow = 1;
            }
        }
        $rt_data['is_follow'] = $is_follow;
        $rt_data['is_muttual'] = $is_muttual;
        $return['code'] = 200;
        $return['data'] = $rt_data;
        return $return;
    }
    
    /**
     * 获取我的关注
     */
    function my_follow($data){
        if(!$data['uid']){
            $return['code'] = 302;
            $return['message'] = '';
            return $return;
        }
        $page = intval($data['page']) ? intval($data['page']) : 1;
        $offset = ($page - 1) * 20;
        $field = 's.fid as uid';
        $map['s.uid'] = $data['uid'];
        $result = M('sns_follow')->alias('s')->join('join dy_member_account a on a.uid = s.fid')
                ->where($map)->field($field)->limit($offset , 20)->select();
        foreach ($result as $key => $value) {
            $muttual = 0;
            $sns_map['uid'] = $value['uid'];
            $sns_map['fid'] = $data['uid'];
            if(M('sns_follow')->where($sns_map)->find()){
                $muttual = 1;
            }
            $result[$key]['muttual'] = $muttual;
            $result[$key]['is_follow'] = 1;
            $userinfo = getUserInfo($value['uid'], 2);
            $result[$key]['nickname'] = $userinfo['nickname'];
            $result[$key]['headimg'] = $userinfo['headimg'];
        }
        $return['code'] = 200;
        $return['data'] = $result;
        return $return;
    }
    
    /**
     * 获取我的粉丝
     */
    function my_fans($data){
        if(!$data['uid']){
            $return['code'] = 302;
            $return['message'] = '';
            return $return;
        }
        $page = intval($data['page']) ? intval($data['page']) : 1;
        $offset = ($page - 1) * 20;
        $field = 's.uid';
        $map['s.fid'] = $data['uid'];
        $result = M('sns_follow')->alias('s')->join('join dy_member_account a on a.uid = s.uid')
                ->where($map)->field($field)->limit($offset , 20)->select();
        foreach ($result as $key => $value) {
            $muttual = 0;
            $is_follow = 0;
            $sns_map['fid'] = $value['uid'];
            $sns_map['uid'] = $data['uid'];
            if(M('sns_follow')->where($sns_map)->find()){
                $muttual = 1;
                $is_follow = 1;
            }
            $result[$key]['muttual'] = $muttual;
            $result[$key]['is_follow'] = $is_follow;
            $userinfo = getUserInfo($value['uid'], 2);
            $result[$key]['nickname'] = $userinfo['nickname'];
            $result[$key]['headimg'] = $userinfo['headimg'];
        }
        $return['code'] = 200;
        $return['data'] = $result;
        return $return;
    }
    
    /**
     * 拉黑/屏蔽某人
     */
    function defriend($data){
        if(!$data['type']){
            $data['type'] = 0;
        }
        if(!$data['uid']){
            $return['code'] = 434;
            return $return;
        }
        if($data['uid'] == UID){
            $return['code'] = 1003;
            return $return;
        }
        $black_member = M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->find();
        switch ($data['type']) {
            case 0:              //拉黑
                if($black_member){
                    if($black_member['is_defriend'] == 0){     //拉黑
                        M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->setField('is_defriend', 1);
                        $type = 'defriend';
                    }else{            //解除拉黑
                        if($black_member['is_shield'] == 0){
                            M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->delete();
                        }else{
                            M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->setField('is_defriend', 0);
                        }
                        $type = 'cancel_defriend';
                    }
                    $return['code'] = 200;
                    $return['data'] = array('type' => $type);
                }else{           //拉黑
                    $black_arr = array(
                        'uid' => UID,
                        'bid' => $data['uid'],
                        'is_defriend' => 1,
                        'is_shield' => 0
                    );
                    if(M('common_blacklist')->add($black_arr)){           //加入黑名单成功
                        $return['code'] = 200;
                    }else{           //加入黑名单失败
                        $return['code'] = 1002;
                    }
                    $return['data'] = array('type' => 'defriend');
                }
                break;
            case 1:              //屏蔽
                if($black_member){
                    if($black_member['is_shield'] == 0){     //屏蔽
                        M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->setField('is_shield', 1);
                        $type = 'shield';
                    }else{            //解除屏蔽
                        if($black_member['is_defriend'] == 0){
                            M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->delete();
                        }else{
                            M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->setField('is_shield', 0);
                        }
                        $type = 'cancel_shield';
                    }
                    $return['code'] = 200;
                    $return['data'] = array('type' => $type);
                }else{           //屏蔽
                    $black_arr = array(
                        'uid' => UID,
                        'bid' => $data['uid'],
                        'is_defriend' => 0,
                        'is_shield' => 1
                    );
                    if(M('common_blacklist')->add($black_arr)){           //屏蔽成功
                        $return['code'] = 200;
                    }else{           //屏蔽失败
                        $return['code'] = 1005;
                    }
                    $return['data'] = array('type' => 'shield');
                }
                break;
            default:
                $return['code'] = 302;
                break;
        }
        return $return;
    }
    
    /**
     * 我的黑名单
     */
    function my_blacklist($data){
        $page = intval($data['page']) ? intval($data['page']) : 1;
        $offset = ($page - 1) * 20;
        $field = 'b.bid as uid';
        $map['b.uid'] = UID;
        $result = M('common_blacklist')->alias('b')->join('join dy_member_account a on a.uid = b.bid')
                ->where($map)->field($field)->limit($offset , 20)->select();
        foreach ($result as $key => $value) {
            $userinfo = getUserInfo($value['uid'], 2);
            $result[$key]['nickname'] = $userinfo['nickname'];
            $result[$key]['headimg'] = $userinfo['headimg'];
        }
        $return['code'] = 200;
        $return['data'] = $result;
        return $return;
    }
    
    /**
     * 移除黑名单
     */
    function remove_blacklist($data){
        if(!$data['uid']){
            $return['code'] = 434;
            return $return;
        }
        if($data['uid'] == UID){
            $return['code'] = 1003;
            return $return;
        }
        if(M('common_blacklist')->where(array('uid' => UID, 'bid' => $data['uid']))->delete()){
            $return['code'] = 200;
        }else{
            $return['code'] = 1006;
        }
        return $return;
    }
}
