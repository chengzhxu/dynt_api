<?php

namespace Content\Logic;

/**
 * 评论相关
 *
 * @author Kevin
 */
class CommentLogic {
    /**
     * 评论
     */
    function comment($data){
        if(!$data['content'] || !$data['objtype'] || !$data['objid']){
            $return['code'] = 302;
            $return['message'] = '';
            return $return;
        }
        $uid = $data['uid'];
        if(!$uid){
            if(!$data['parent_id']){
                $table = getTable($data['objtype']);
                $uid = M($table)->where(array('id' => $data['objid']))->getField('uid');
            }else{
                $uid = M('common_comment')->where(array('id' => $data['parent_id']))->getField('from_uid');
            }
        }
        $comment_arr = array(
            'content' => emoij_to_ubb($data['content']),
            'from_uid' => UID,
            'to_uid' => $uid ? $uid : 0,
            'parent_id' => $data['parent_id'] ? $data['parent_id'] : 0,
            'objtype' => $data['objtype'],
            'objid' => $data['objid'],
            'dateline' => NOW_TIME
        );
        $comment_id = M('common_comment')->add($comment_arr);
        $new_comment = array();
        if($comment_id){    //评论成功
            //返回当前评论对象
            $new_comment = M('common_comment')->where(array('id' => $comment_id))->select();
            $new_comment = $this->commentlist($new_comment);
            
            $from_user = getUserInfo(UID, 1);
            $msg_arr =array(
                'from_uid' => UID,
                'to_uid' => $uid,
                'title' => $from_user['nickname'] . '评论了你',
                'content' => emoij_to_ubb($data['content']),
                'type' => 1,
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
        $return['code'] = 200;
        $return['data'] = array('comment_id' => $comment_id, 'comment' => $new_comment);
        return $return;
    }
    
    /**
     * 获取相关对象评论数量
     */
    function get_comment_count($objtype, $objid){
        if(!$objtype || !$objid){
            return 0;
        }
        return M('common_comment')->where(array('objtype' => $objtype, 'objid' => $objid, 'deleted' => 0))->count();
    }
    
    /**
     * 获取相关对象评论列表
     */
    function get_comment_list($objtype, $objid, $page = 1){
        if(!$objtype || !$objid){
            return array();
        }
        $offset = ($page - 1) * C('PAGESIZE');
        $where['objtype'] = $objtype;
        $where['objid'] = $objid;
        $where['parent_id'] = 0;
        $where['deleted'] = 0;
        $commentlist = M('common_comment')->where($where)->order('dateline desc')->limit($offset , C('PAGESIZE'))->select();
        $commentlist = $this->commentlist($commentlist);
//        if($page == 1){
//            $total_page = 0;
//            $total_count = M('common_comment')->where($where)->count();
//            if($total_count > 0){
//                $total_page = ceil($total_count / C('PAGESIZE'));
//            }
//            return array('code' => 200, 'data' => $commentlist, 'page_size' => C('PAGESIZE'), 'total_page' => $total_page);
//        }else{
            return array('code' => 200, 'data' => $commentlist);
//        }
    }
    
    
    
    function commentlist($result){
        $newdata = array();
                
        if($result) {
            $Date = new \Org\Util\Date();
            foreach($result as $value) {
                $userinfo = getUserInfo($value['from_uid'] , 2);
                $value['from_headimg'] = !empty($userinfo['headimg']) ? $userinfo['headimg'] : getDefaultHeadimg($value['from_uid']);
                $value['from_nick']= !empty($userinfo['nickname']) ? $userinfo['nickname'] : '路人甲';
                $value['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $value['dateline']));
                $u = getUserInfo($value['to_uid'] , 2);
                $value['to_headimg'] = !empty($u['headimg']) ? $u['headimg'] : getDefaultHeadimg($value['to_uid']);
                $value['to_nick'] = !empty($u['nickname']) ? $u['nickname'] : '路人丙';
				
                $value['content'] = ubb_to_emoij($value['content']);
				$newreply = getReply($value['id']);
//				$newreply = arraySingle($newreply,1);
                $value['reply'] = $newreply;
                $newdata[] = $value;
                unset($value);
            }
        }
        return $newdata;
    }
    
    /**
     * 删除评论
     */
    function del_comment($data){
        if(!$data['id']){
            return array('code' => 302);
        }
        $comment = M('common_comment')->where(array('id' => $data['id']))->find();
        if($comment['deleted'] == 1){
            return array('code' => 803);
        }
        $author_id = $this->get_author_by_comment($data['id']);            //当前帖子的发布者uid
        if($comment['from_uid'] == UID || $author_id == UID){
            if(M('common_comment')->where(array('id' => $data['id']))->setField('deleted', 1)){
                if($comment['parent_id'] == 0){
                    $this->del_reply($data['id']);
                }else{
                    M('common_comment')->where(array('parent_id' => $comment['id']))->setField('parent_id', $comment['parent_id']);
                }
                $to_uid = M('common_comment')->where(array('id' => $data['id']))->getField('to_uid');
                return array('code' => 200, 'data' => array('to_uid' => $to_uid));
            }else{
                return array('code' => 802);
            }
        }else{
            return array('code' => 801);
        }
    }
    
    /**
     * 删除评论的子评论
     */
    function del_reply($parent_id = 0){
        if($parent_id){
            $reply_list = M('common_comment')->where(array('parent_id' => $parent_id))->select();
            M('common_comment')->where(array('parent_id' => $parent_id))->setField('deleted', 1);
            foreach ($reply_list as $key => $value) {
                $this->del_reply($value['id']);
            }
        }
    }
    
    /**
     * 根据评论获取对应帖子作者
     */
    function get_author_by_comment($comment_id){
        $author_id = 0;
        $comment = M('common_comment')->where(array('id' => $comment_id))->find();
        if($comment){
            switch ($comment['objtype']) {
                case 1:          //话题
                    $author_id = M('common_topic')->where(array('id' => $comment['objid']))->getField('uid');
                    break;
                case 2:          //招聘
                    $author_id = M('common_recruit')->where(array('id' => $comment['objid']))->getField('uid');
                    break;
                case 3:          //求职
                    $author_id = M('common_job')->where(array('id' => $comment['objid']))->getField('uid');
                    break;
            }
        }
        return $author_id;
    }
}
