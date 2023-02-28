<?php

namespace Content\Logic;

/**
 * 消息管理
 *
 * @author Kevin
 */
class MessageLogic {
    /**
     * 新增消息
     */
    function add_message($data){
        if($data){
            $data['from_uid'] = UID;
            $data['dateline'] = NOW_TIME;
            $msg_id = M('common_message')->add($data);
            if($msg_id){
                $return = array('code' => 200, 'data' => array('id' => $msg_id));
            }else{
                $return = array('code' => 901);
            }
            return $return;
        }
    }
    
    /**
     * 获取消息列表
     */
    function get_message_list($data){
        $page = intval($data['page']) ? intval($data['page']) : 1;
        $data['type'] = 0;
        $offset = ($page - 1) * C('PAGESIZE');
        $register_time = M('member_account')->where(array('uid' => UID))->getField('dateline');
        
        $map['cm.deleted'] = 0;
        $map['cm.content'] = array('neq', '');
        $map['cm.to_uid'] = array(array('eq', 0), array('eq', UID), 'OR');
        if($data['type'] > -1){  //-1 获取全部
            $map['cm.type'] = $data['type'];   //消息类型(0:系统消息;1:评论;2:点赞)
        }
        $map['_string'] = 'md.id is null and cm.dateline >= '. $register_time;
        $field = 'cm.id, cm.from_uid, cm.title, cm.content, cm.type, cm.objtype, cm.objid, cm.readed, cm.dateline, cm.to_uid';
        $list = M('common_message')->alias('cm')
                ->join('dy_common_message_del md on md.msg_id = cm.id and md.uid = ' . UID, 'LEFT')
                ->where($map)->field($field)->order('cm.dateline desc')->limit($offset , C('PAGESIZE'))->select();
        $Date = new \Org\Util\Date();
        foreach ($list as $key => $value) {
            $from_user = getUserInfo($value['from_uid'], 2);
            $list[$key]['from_nickname'] = $from_user['nickname'];
            $list[$key]['from_headimg'] = $from_user['headimg'];
            $list[$key]['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $value['dateline']));
            $list[$key]['content'] = ubb_to_emoij($value['content']);
            if($value['to_uid'] == 0){   //发送给所有人的消息
                if(M('common_message_readed')->where(array('uid'=>UID, 'msg_id'=>$value['id']))->find()){
                    $list[$key]['readed'] = 1;
                }
            }
        }
        
        //设为已读
        $this->read_message(array('id' => 0));
        
        return array('code' => 200, 'data' => $list);
    }
    
    /**
     * 消息设为已读
     */
    function read_message($data){
        $map['to_uid'] = UID;
        if($data['id'] > 0){        //读指定消息
            $map['id'] = $data['id'];
            $message = M('common_message')->where(array('id' => $data['id']))->find();
            if($message['to_uid'] == 0){     //发送给所有人的消息
                if(!M('common_message_readed')->where(array('uid'=>UID,'msg_id'=>$data['id']))->find()){
                    $read_arr = array(
                        'uid' => UID,
                        'msg_id' => $data['id'],
                        'dateline' => time()
                    );
                    M('common_message_readed')->add($read_arr);
                }
            }else{
                M('common_message')->where($map)->setField('readed', 1);
            }
        }else{         //读所有消息
            M('common_message')->where($map)->setField('readed', 1);
            $last_msg = M('common_message_readed')->where(array('uid' => UID))->order('msg_id desc')->find();
            
            $msg_map['to_uid'] = 0;
            $msg_map['deleted'] = 0;
            
            if($last_msg){
                $msg_map['id'] = array('gt', $last_msg['msg_id']);
                $msg_list = M('common_message')->where($msg_map)->select();
            }else{
                $msg_list = M('common_message')->where($msg_map)->select();
            }
            $red_arr = array();
            foreach ($msg_list as $key => $value) {
                $red_arr[$key] = array(
                    'uid' => UID,
                    'msg_id' => $value['id'],
                    'dateline' => time()
                );
            }
            if(count($red_arr) > 0){
                M('common_message_readed')->addAll($red_arr);
            }
        }
        
        return array('code' => 200);
    }
    
    /**
     * 删除消息
     */
    function del_message($data){
        $message = M('common_message')->where(array('id' => $data['id']))->find();
        if(!$message){
            return array('code' => 902);
        }
        if($message['to_uid'] != 0 && $message['to_uid'] != UID){
            return array('code' => 903);
        }
        if($message['deleted'] == 1){
            return array('code' => 904);
        }
        if($message['to_uid'] == 0){     //发送给所有人的消息
            $del_arr = array(
                'uid' => UID,
                'msg_id' => $data['id'],
                'dateline' => time()
            );
            M('common_message_del')->add($del_arr);
        }else{
            if(M('common_message')->where(array('id' => $data['id']))->setField('deleted', 1)){
                return array('code' => 200);
            }else{
                return array('code' => 905);
            }
        }
    }
    
    /**
     * 获取未读消息数
     */
    function getNotReadMsg($data){
        $register_time = M('member_account')->where(array('uid' => UID))->getField('dateline');
        $map['deleted'] = 0;
        $map['to_uid'] = array(array('eq', 0), array('eq', UID), 'OR');
        $map['readed'] = 0;
        $map['dateline'] = array('egt', $register_time);
        
        $to_all_map['to_uid'] = 0;
        $to_all_map['deleted'] = 0;
        $to_all_map['dateline'] = array('egt', $register_time);
        $msg_list = M('common_message')->where($to_all_map)->field('id')->select();        //发送给所有人的消息
        $ed_count = 0;    //已删除或已读的消息数
        foreach ($msg_list as $key => $value) {
            $readed = M('common_message_readed')->where(array('uid'=>UID,'msg_id'=>$value['id']))->find();
            $deled = M('common_message_del')->where(array('uid'=>UID,'msg_id'=>$value['id']))->find();
            if($readed || $deled){
                $ed_count++;
            }
        }
        
        //未读消息数
        $msg_count = M('common_message')->where($map)->count() - $ed_count;
        if($msg_count < 0){
            $msg_count = 0;
        }
        
        //系统消息未读数
        $map['type'] = 0;
        $system_count = M('common_message')->where($map)->count() - $ed_count;
        if($system_count < 0){
            $system_count = 0;
        }
        
        //评论消息未读数
        $map['type'] = 1;
        $comment_count = M('common_message')->where($map)->count();
        
        //点赞消息未读数
        $map['type'] = 2;
        $priase_count = M('common_message')->where($map)->count();
        
        
        $favorite_count = M('common_favorite')->where(array('uid' => UID))->count();  //收藏数量
        $fans_count = M('sns_follow')->where(array('fid' => UID))->count();     //粉丝数量
        $follow_count = M('sns_follow')->where(array('uid' => UID))->count();   //关注数量
        
        //话题页评论未读数(类似微信)
        $read = M('common_comment_read')->where(array('uid' => UID))->find();
        if(!$read){
            $last_read = time();
            $read_arr = array(
                'uid' => UID,
                'dateline' => $last_read
            );
            M('common_comment_read')->add($read_arr);
        }else{
            $last_read = $read['dateline'];
        }
        $topic_comment_map['to_uid'] = UID;
        $topic_comment_map['objtype'] = 1;
        $topic_comment_map['dateline'] = array('egt', $last_read);
        $topic_comment_map['deleted'] = 0;
        $topic_comment_count = M('common_comment')->where($topic_comment_map)->count();

        $return['code'] = 200;
        $return['data'] = array(
            'msg_count' => $msg_count,
            'system_count' => $system_count,
            'comment_count' => $comment_count, 
            'priase_count' => $priase_count,
            'favorite_count' => $favorite_count,
            'fans_count' => $fans_count,
            'follow_count' => $follow_count,
            'topic_comment_count' => $topic_comment_count
        );
        return $return;
    }
    
    /**
     * 话题评论未读列表
     */
    function get_unread_comment($data){
        $last_read = M('common_comment_read')->where(array('uid' => UID))->find();  //最后读取时间
        $topic_comment_map['to_uid'] = UID;
        $topic_comment_map['objtype'] = 1;     //预留，当前只取话题
        $topic_comment_map['dateline'] = array('egt', $last_read['dateline']);
        $topic_comment_map['deleted'] = 0;
        $field = 'id,content,from_uid,to_uid,objtype,objid,dateline';
        $comment_list = M('common_comment')->where($topic_comment_map)->field($field)->select();
        $to_info = getUserInfo(UID , 2);
        $Date = new \Org\Util\Date();
        foreach ($comment_list as $key => $value) {
            $userinfo = getUserInfo($value['from_uid'] , 2);
            $comment_list[$key]['from_headimg'] = !empty($userinfo['headimg']) ? $userinfo['headimg'] : getDefaultHeadimg($value['from_uid']);
            $comment_list[$key]['from_nick']= !empty($userinfo['nickname']) ? $userinfo['nickname'] : '路人甲';
            $comment_list[$key]['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $value['dateline']));
            $comment_list[$key]['content'] = ubb_to_emoij($value['content']);
            
            $comment_list[$key]['to_headimg'] = !empty($to_info['headimg']) ? $to_info['headimg'] : getDefaultHeadimg($value['to_uid']);
            $comment_list[$key]['to_nick'] = !empty($to_info['nickname']) ? $to_info['nickname'] : '路人丙';
            if($value['objtype'] == 1){
                $topic_content = M('common_topic')->where(array('id' => $value['objid']))->getField('content');
                $comment_list[$key]['topic_content'] = ubb_to_emoij($topic_content);
                $topic_detail = M('common_topic_detail')->where(array('topic_id' => $value['objid']))->order('id')->find();
                if($topic_detail){
                    $comment_list[$key]['topic_image'] = $topic_detail['location'];
                }else{
                    $comment_list[$key]['topic_image'] = '';
                }
            }
        }
        M('common_comment_read')->where(array('uid' => UID))->setField('dateline', time());
        return array('code' => 200, 'data' => $comment_list);
    }
}
