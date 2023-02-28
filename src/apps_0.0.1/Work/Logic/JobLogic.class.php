<?php

namespace Work\Logic;

/**
 * 求职相关
 *
 * @author Kevin
 */
class JobLogic {
    /**
     * 获取求职信息列表
     */
    function get_job_list($data){
        $page = intval($data['page']) ? intval($data['page']) : 1;
        $offset = ($page - 1) * C('PAGESIZE');
        $map['deleted'] = 0;
        if($data['uid']){          //指定用户的招聘信息
            $map['uid'] = $data['uid'];
        }
        $field = 'id, uid, position_name, mobile, realname, job_status, salary, address, education, experience, age, gender, introduce, dateline, display_order';
        $order['display_order'] = 'desc';
        $order['dateline'] = 'desc';
        $list = M('common_job')->where($map)->field($field)->order($order)->limit($offset , C('PAGESIZE'))->select();
        foreach ($list as $key => $value) {
            $list[$key] = fixed_job($value);
        }
        return array('code' => 200, 'data' => $list);
    }
    
    /**
     * 发布求职信息
     */
    function add_job($data){
        if(!$data){
            return array('code' => 701);
        }
        if(!$data['position_name']){
            return array('code' => 702);
        }
        if(!$data['mobile'] || !validate_mobile($data['mobile'])){
            return array('code' => 704);
        }
        if(!$data['introduce']){
            return array('code' => 712);
        }
        $arr = array(
            'uid' => UID,
            'position_name' => $data['position_name'],
            'job_status' => $data['job_status'] ? $data['job_status'] : 0,
            'mobile' => $data['mobile'],
            'realname' => $data['realname'],
            'salary' => $data['salary'] ? $data['salary'] : 1,
            'address' => $data['address'] ? $data['address'] : 1,
            'education' => $data['education'] ? $data['education'] : 1,
            'experience' => $data['experience'] ? $data['experience'] : 1,
            'age' => $data['age'] ? $data['age'] : 25,
            'gender' => $data['gender'] ? $data['gender'] : 1,
            'introduce' => emoij_to_ubb($data['introduce']),
            'dateline' => NOW_TIME
        );
        $id = M('common_job')->add($arr);
        if($id){
            return array('code' => 200, 'data' => array('id' => $id));
        }else{
            return array('code' => 706);
        }
    }
    
    /**
     * 求职信息详情
     */
    function get_job_detail($data){
        if(!$data['id']){
            return array('code' => 302);
        }
        $job = M('common_job')->where(array('id' => $data['id']))->find();
        if(!$job){
            return array('code' => 707);
        }
        if($job['deleted'] == 1){
            return array('code' => 708);
        }
        $Date = new \Org\Util\Date();
        $job['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $job['dateline']));
        $job['objtype'] = 3;
        $job['objid'] = $data['id'];
        $job['salary'] = get_select('WORK_SALARY', $job['salary']);   //薪资范围
        $job['education'] = get_select('WORK_EDUCATION', $job['education']);   //学历
        $job['experience'] = get_select('WORK_EXPERIENCE', $job['experience']);   //经验
        $job['gender'] = get_select('WORK_GENDER', $job['gender']);   //性别
        $job['job_status'] = get_select('WORK_STATUS', $job['job_status']);   //工作状态
        $job['address'] = get_select('WORK_ADDRESS', $job['address']);   //地区
        $job['introduce'] = ubb_to_emoij($job['introduce']);
        if($job['uid']){
            $userinfo = getUserInfo($job['uid'], 2);
            $job['nickname'] = $userinfo['nickname'];
            $job['headimg'] = $userinfo['headimg'];
            
            $is_follow = 0;           //是否关注当前用户
            if(M('sns_follow')->where(array('uid' => UID, 'fid' => $job['uid']))->find()){
                $is_follow = 1;
            }
            $job['is_follow'] = $is_follow;
        }
        $praise_logic = D('Content/Praise', 'Logic');
        $job['praise_count'] = $praise_logic->get_praise_count(array('objtype' => 3, 'objid' => $data['id']));        //点赞数量
        $job['is_praise'] = $praise_logic->get_praise_status(array('uid' => UID, 'objtype' => 3, 'objid' => $data['id']));        //点赞状态
        
        $favorite_logic = D('Content/Favorite', 'Logic');
        $fav_map = array(
            'uid' => UID,
            'objtype' => 3,
            'objid' => $data['id']
        );
        $is_favorite = 0;          //收藏状态
        if($favorite_logic->is_favorite($fav_map)){
            $is_favorite = 1;
        }
        $job['is_favorite'] = $is_favorite;
        
        $comment_logic = D('Content/Comment','Logic');
        $comment_count = $comment_logic->get_comment_count(3, $data['id']);         //评论数量
        $comment_list = $comment_logic->get_comment_list(3, $data['id']);
        $comment_list = $comment_list['data'];
        $job['comment_count'] = $comment_count;
        $job['comment_list'] = $comment_list;
        
        $share = array(
            'share_title' => '鸟听求职',
            'share_content' => $job['position_name'],
            'share_img' => 'https://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/logo.png',
            'share_url' => 'http://api.danyangniaoting.com/share?type=3&id='.$data['id'],
            'share_type' => 'job'
        );
        $job['share'] = $share;
        return array('code' => 200, 'data' => $job);
    }
    
    /**
     * 删除求职信息
     */
    function del_job($data){
        if(!$data['id']){
            return array('code' => 707);
        }
        $job = M('common_job')->where(array('id' => $data['id']))->find();
        if($job['uid'] != UID){
            return array('code' => 709);
        }
        if($job['deleted'] == 1){
            return array('code' => 708);
        }
        if(M('common_job')->where(array('id' => $data['id']))->setField('deleted', 1)){
            return array('code' => 200);
        }else{
            return array('code' => 711);
        }
    }
}
