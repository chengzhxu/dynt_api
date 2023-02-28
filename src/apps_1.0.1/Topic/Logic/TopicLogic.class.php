<?php

namespace Topic\Logic;
/**
 * 话题接口相关
 *
 * @author Kevin
 */
class TopicLogic {
    function __construct() {
        
    }
    /**
     * 发布话题
     */
    function add_topic($data){
        $oss = D('Common/Oss' , 'Logic');
        $topicArray = $data;
        //发布话题
        $topic_arr = array(
            'content' => emoij_to_ubb($topicArray['content']),
            'uid' => UID,
            'dateline' => NOW_TIME,
            'type' => $data['type'] ? $data['type'] : 0
        );
        $topic_id = M('common_topic')->add($topic_arr);
        $thumb_arr = $data['thumbs'];
        $topic_detail = array();
        foreach ($thumb_arr as $key => $value) {
            $value = (array)$value;
            $filename = getMillisecond().'.jpg';
            $response = $oss->upload_by_multi_part($value["name"]["tmp_name"], 'upload' . date('Y-m-d', NOW_TIME),$filename,$value['name']['size']);
            if($response['status'] == 200) {
               $path = $response['url'];
            }
            if($path){
                $topic_detail[$key] = array('topic_id' => $topic_id, 'location' => $path);
            }
        }
        if(count($topic_detail) > 0){
            M('common_topic_detail')->addAll($topic_detail);
        }
        $return['code'] = 200;
        $return['data'] = array('topic_id' => $topic_id);
        return $return;
    }
    
    /**
     * 获取话题列表
     */
    function get_topic_list($data){
        $page = intval($data['page']) ? intval($data['page']) : 1;
        $page_size = 10;
        $offset = ($page - 1) * $page_size;
        if(!$data['type']){
            $data['type'] = 0;
        }
        $map['deleted'] = 0;
        if($data['column_id']){
            $map['column_id'] = $data['column_id'];
        }
        $order['display_order'] = 'desc';
        $order['dateline'] = 'desc';
        
        $black_uids = array();
        if(UID){
            $black_list = M('common_blacklist')->where(array('uid' => UID))->field('bid')->select();
            foreach ($black_list as $key => $value) {
                $black_uids[] = $value['bid'];
            }
        }
        
        $field = 'id, content, uid, dateline, type as topic_type, display_order';
        if($data['type'] == 0){            //最新话题
            if($data['uid']){       //指定用户发布话题
                $map['uid'] = $data['uid'];
            }else{
                if(UID && count($black_uids) > 0){
                    $map['uid'] = array('not in', $black_uids);
                }
            }
            $topic_list = M('common_topic')
                    ->where($map)->field($field)->order($order)->limit($offset , $page_size)->select();
        }else if($data['type'] == 1){          //关注用户发布的话题
            //获取关注用户uid
            $topic_list = array();
            if(UID){
                $uids = M('SnsFollow')->where( array('uid'=>UID,'status'=>0) )->field('fid')->select();
                foreach($uids as $value){
                        $newUid[] = $value['fid'];
                }
                $newUid[] = UID;
                $uids = $newUid;
                
                if(count($black_uids) > 0){
                    $map['uid'] = array(array('in',$uids), array('not in', $black_uids), 'and');
                }else{
                    $map['uid'] = array('in',$uids);
                }
                
                $topic_list = M('common_topic')
                        ->where($map)->field($field)->order($order)->limit($offset , $page_size)->select();
            }
        }
        foreach ($topic_list as $key => $value) {
            $new_val = fixed_topic($value);
            $topic_list[$key] = $new_val;
        }
        return array('code' => 200, 'data' => $topic_list);
    }
    
    /**
     * 获取话题详情
     */
    function get_topic_detail($data){
        if(!$data['topic_id']){
            $return['code'] = 302;
            $return['message'] = '';
            return $return;
        }
        $result = array();
        $topic = M('common_topic')->where(array('id' => $data['topic_id']))->find();        //话题详情
        if($topic['deleted'] == 1){
            return array('code' => 604);
        }
        if($topic){
            $Date = new \Org\Util\Date();
            $topic_detail = M('common_topic_detail')->where(array('topic_id' => $data['topic_id']))->select();
            foreach ($topic_detail as $key => $value) {
                $ratio = getImageRatio($value['location']);
                $topic_detail[$key]['ratio'] = $ratio;
                $thumb_width = 300;
                $thumb_height = round($thumb_width / $ratio);
                $resimgurl = str_replace('http://','',$value['location']);
                $resimgurl = str_replace('/','_',$resimgurl);
                $topic_detail[$key]['small_thumb'] = 'http://api.danyangniaoting.com/util/image/Resizerimg/w/'.$thumb_width.'/h/'.$thumb_height.'/url/'.$resimgurl;
                
                $imgType = pathinfo($value['location'],PATHINFO_EXTENSION );
                $topic_detail[$key]['img_type'] = $imgType;
                $fp=fopen($value['location'],'rb');  
                 $image_head = fread($fp,1024);  
                fclose($fp);  
                 if(preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)){
                     $topic_detail[$key]['img_type'] = 'gif';
                 }
            }
            $topic['detail_arr'] = $topic_detail;
            $topic['objtype'] = 1;
            $topic['objid'] = $data['topic_id'];
            $topic['content'] = ubb_to_emoij($topic['content']);
            $topic['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $topic['dateline']));
        }
        $result['topic'] = $topic;
        $comment_logic = D('Content/Comment','Logic');
        $comment_count = $comment_logic->get_comment_count(1, $data['topic_id']);         //评论数量
        $comment_list = $comment_logic->get_comment_list(1, $data['topic_id']);
//        $result['page_size'] = $comment_list['page_size'];            //每页条数
//        $result['total_page'] = $comment_list['total_page'];        //总页数
        $comment_list = $comment_list['data'];       //评论列表
        
        $result['comment_count'] = $comment_count;
        $result['comment_list'] = $comment_list;
        
        $is_follow = 0;           //是否关注当前用户
        if(M('sns_follow')->where(array('uid' => UID, 'fid' => $topic['uid']))->find()){
            $is_follow = 1;
        }
        $result['is_follow'] = $is_follow;
        $userinfo = getUserInfo($topic['uid'], 2);
        
        $result['nickname'] = $userinfo['nickname'];
        $result['headimg'] = $userinfo['headimg'];
        
        $praise_logic = D('Content/Praise', 'Logic');
        $praise_count = $praise_logic->get_praise_count(array('objtype' => 1, 'objid' => $data['topic_id']));        //点赞数量
        
        $result['praise_count'] = $praise_count;
        $result['is_praise'] = $praise_logic->get_praise_status(array('uid' => UID, 'objtype' => 2, 'objid' => $data['topic_id']));        //点赞数量
        
        $favorite_logic = D('Content/Favorite', 'Logic');
        $fav_map = array(
            'uid' => UID,
            'objtype' => 1,
            'objid' => $data['topic_id']
        );
        $is_favorite = 0;          //收藏状态
        if($favorite_logic->is_favorite($fav_map)){
            $is_favorite = 1;
        }
        $result['is_favorite'] = $is_favorite;
        $is_myself = 0;       //当前话题是否是本人发布的
        if($topic['uid'] == UID){
            $is_myself = 1;
        }
        $result['is_myself'] = $is_myself;
        
        if($topic['detail_arr']){
            $share_img = $topic['detail_arr'][0]['location'];
        }else{
            $share_img = 'https://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/logo.png';
        }
        $share_img = str_replace('http://','https://',$share_img);
        if($topic['content']){
            $share_title = mb_substr(strip_tags($topic['content']), 0, 30, 'utf8');
            $share_content = mb_substr(strip_tags($topic['content']), 0, 60, 'utf8');
        }else{
            $share_title = '鸟听话题';
            $share_content = '鸟听话题';
        }
        
        $share = array(
            'share_title'=>$share_title,
            'share_content'=>$share_content,
            'share_img' => $share_img,
            'share_url' => 'http://api.danyangniaoting.com/share?type=1&id='.$data['topic_id'],
            'share_type' => 'topic'
        );
        $result['share'] = $share;
        
        
        $return['code'] = 200;
        $return['data'] = $result;
        return $return;
    }
    
    /**
     * 删除话题
     */
    function del_topic($data){
        if(!$data['topic_id']){
            $return['code'] = 302;
            return $return;
        }
        $topic = M('common_topic')->where(array('id' => $data['topic_id']))->find();
        if(!$topic){
            $return['code'] = 601;
            return $return;
        }
        if($topic['uid'] != UID){
            $return['code'] = 603;
            return $return;
        }
        if(M('common_topic')->where(array('id' => $data['topic_id']))->setField('deleted', 1)){
            //主题标记删除，附件物理删除
            $detail_list = M('common_topic_detail')->where(array('topic_id' => $data['topic_id']))->select();
            foreach ($detail_list as $key => $value) {
                $filearr = explode('/',$value['location']);
                $index = count($filearr) - 1;
                $filename = $filearr[$index];
                $filepath = 'uploads/topic/' . $filename;
                unlink($filepath);    //删除服务器文件
            }
            M('common_topic_detail')->where(array('topic_id' => $data['topic_id']))->delete();
            $return['code'] = 200;
        }else{
            $return['code'] = 602;
        }
        return $return;
    }
    
    /**
     * 获取话题栏目列表
     */
    function get_topic_column_list(){
        $map['deleted'] = 0;
        $order['dateline'] = 'desc';
        $field = 'id, name';
        $list = M('common_topic_column')->where($map)->field($field)->order($order)->select();
        foreach ($list as $key => $value) {
            $list[$key]['column_id'] = $value['id'];
        }
        return array('code' => 200, 'data' => $list);
    }
    
}
