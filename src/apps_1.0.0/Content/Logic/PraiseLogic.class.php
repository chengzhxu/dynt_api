<?php

namespace Content\Logic;

/**
 * 点赞相关
 *
 * @author Kevin
 */
class PraiseLogic {
    /**
     * (取消)点赞 
     */
    function praise($data){
        if(!$data['objtype'] || !$data['objid']){
            $return['code'] = 302;
            $return['message'] = '';
            return $return;
        }
        $type = 'praise';
        $praise = M('common_praise')->where(array('objtype' => $data['objtype'], 'objid' => $data['objid']))->find();
        if(!$praise){        //点赞
            
            $uid = $data['uid'];
            if(!$uid){
                $table = getTable($data['objtype']);
                $uid = M($table)->where(array('id' => $data['objid']))->getField('uid');
            }
            
            $praise_arr = array(
                'from_uid' => UID,
                'to_uid' => $uid ? $uid : 0,
                'objtype' => $data['objtype'],
                'objid' => $data['objid'],
                'dateline' => NOW_TIME
            );
            $praise_id = M('common_praise')->add($praise_arr);
            if($praise_id){     //点赞成功
                $from_user = getUserInfo(UID, 1);
                $msg_arr =array(
                    'from_uid' => UID,
                    'to_uid' => $uid,
                    'title' => $from_user['nickname'] . '点赞了你',
                    'content' => $data['content'],
                    'type' => 2,
                    'objtype' => $data['objtype'],
                    'objid' => $data['objid'],
                    'dateline' => NOW_TIME
                );
                addMessage($msg_arr);             //保存消息
                
                //发送通知
                
            }else{
                $return['code'] = 303;
                $return['message'] = '';
                return $return;
            }
        }else{           //取消点赞
            $type = 'cancel_praise';
            M('common_praise')->where(array('objtype' => $data['objtype'], 'objid' => $data['objid']))->delete();
        }
        $return['code'] = 200;
        $return['data'] = array('type' => $type);
        return $return;
    }
    
    /**
     * 获取对象点赞数量
     */
    function get_praise_count($data){
        if(!$data['objtype'] || !$data['objid']){
            return 0;
        }
        return M('common_praise')->where(array('objtype' => $data['objtype'], 'objid' => $data['objid']))->count() ? 
                M('common_praise')->where(array('objtype' => $data['objtype'], 'objid' => $data['objid']))->count() : 0;
    }
    
    /**
     * 获取对象点赞状态
     */
    function get_praise_status($data){
        if(!$data['objtype'] || !$data['objid'] || !$data['uid']){
            return 0;
        }
        $map = array(
            'from_uid' => $data['uid'],
            'objtype' => $data['objtype'],
            'objid' => $data['objid'],
        );
        if(M('common_praise')->where($map)->find()){
            return 1;
        }else{
            return 0;
        }
    }
}
