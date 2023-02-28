<?php

namespace Work\Logic;

/**
 * 招聘相关
 *
 * @author Kevin
 */
class RecruitLogic {
    /**
     * 获取工作要求选项
     */
    function get_work_option($data){
        $result = array();
        $education = get_select_double('WORK_EDUCATION');      //学历
        $experience = get_select_double('WORK_EXPERIENCE');       //相关经验
        if($data['type'] == 1){              //求职
            $result['status'] = get_select_double('WORK_STATUS');          //求职者目前工作状态
            $result['address'] = getAllAddress();        //工作地点
            if($education[0]['value'] == '不限'){
                array_shift($education);
            }
            if($experience[0]['value'] == '不限'){
                array_shift($experience);
            }
            $result['gender'] = array(array('id' => 0, 'value' => '女'),array('id' => 1, 'value' => '男'));     //性别要求
//            $result['age'] = getJobAge();
        }else{          //招聘
            $result['address'] = getAllAddress(1);        //工作地点
            $result['gender'] = get_select_double('WORK_GENDER');     //性别要求
            $result['age'] = get_select_double('WORK_AGE');           //年龄要求
        }
        $result['salary'] = get_select_double('WORK_SALARY');   //薪资范围
        $result['education'] = $education;      //学历
        $result['experience'] = $experience;       //相关经验
        return array('code' => 200, 'data' => $result);
    }
    
    /**
     * 新增招聘信息
     */
    function add_recruit($data){
        if(!$data){
            return array('code' => 701);
        }
        if(!$data['position_name']){
            return array('code' => 702);
        }
        if(!$data['company_name']){
            return array('code' => 703);
        }
        if(!$data['mobile'] || !validate_mobile($data['mobile'])){
            return array('code' => 704);
        }
        if(!$data['duty']){
            return array('code' => 705);
        }
        $arr = array(
            'uid' => UID,
            'position_name' => $data['position_name'],
            'company_name' => $data['company_name'],
			'address_detail' => $data['address_detail'],
            'mobile' => $data['mobile'],
			'realname' => $data['realname'],
            'salary' => $data['salary'] ? $data['salary'] : 0,
            'address' => $data['address'] ? $data['address'] : 0,
            'education' => $data['education'] ? $data['education'] : 0,
            'experience' => $data['experience'] ? $data['experience'] : 0,
            'age' => $data['age'] ? $data['age'] : 0,
            'gender' => $data['gender'] ? $data['gender'] : 0,
            'duty' => emoij_to_ubb($data['duty']),
            'dateline' => NOW_TIME
        );
        $id = M('common_recruit')->add($arr);
        if($id){
            return array('code' => 200, 'data' => array('id' => $id));
        }else{
            return array('code' => 706);
        }
    }
    
    /**
     * 招聘列表
     */
    function get_recruit_list($data){
        $page = intval($data['page']) ? intval($data['page']) : 1;
        $offset = ($page - 1) * C('PAGESIZE');
        $map['deleted'] = 0;
        if($data['uid']){          //指定用户的招聘信息
            $map['uid'] = $data['uid'];
        }
        $field = 'id, uid, position_name, company_name, address_detail, mobile, realname, salary, address, education, experience, age, gender, duty, dateline, display_order';
        $order['display_order'] = 'desc';
        $order['dateline'] = 'desc';
        $list = M('common_recruit')->where($map)->field($field)->order($order)->limit($offset , C('PAGESIZE'))->select();
        foreach ($list as $key => $value) {
            $list[$key] = fixed_recruit($value);
        }
        return array('code' => 200, 'data' => $list);
    }
    
    /**
     * 招聘信息详情
     */
    function recruit_detail($data){
        if(!$data['id']){
            return array('code' => 302);
        }
        $recruit = M('common_recruit')->where(array('id' => $data['id']))->find();
        if(!$recruit){
            return array('code' => 707);
        }
        if($recruit['deleted'] == 1){
            return array('code' => 708);
        }
        $Date = new \Org\Util\Date();
        $recruit['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $recruit['dateline']));
        $recruit['objtype'] = 2;
        $recruit['objid'] = $data['id'];
        $recruit['salary'] = get_select('WORK_SALARY', $recruit['salary']);   //薪资范围
        $recruit['education'] = get_select('WORK_EDUCATION', $recruit['education']);   //学历
        $recruit['experience'] = get_select('WORK_EXPERIENCE', $recruit['experience']);   //经验
        $recruit['age'] = get_select('WORK_AGE', $recruit['age']);   //年龄
        $recruit['gender'] = get_select('WORK_GENDER', $recruit['gender']);   //性别
        $recruit['address'] = get_select('WORK_ADDRESS', $recruit['address']);   //地区
        $recruit['duty'] = ubb_to_emoij($recruit['duty']);
        if($recruit['uid']){
            $userinfo = getUserInfo($recruit['uid'], 2);
            $recruit['nickname'] = $userinfo['nickname'];
            $recruit['headimg'] = $userinfo['headimg'];
            
            $is_follow = 0;           //是否关注当前用户
            if(M('sns_follow')->where(array('uid' => UID, 'fid' => $recruit['uid']))->find()){
                $is_follow = 1;
            }
            $recruit['is_follow'] = $is_follow;
        }
        $praise_logic = D('Content/Praise', 'Logic');
        $recruit['praise_count'] = $praise_logic->get_praise_count(array('objtype' => 2, 'objid' => $data['id']));        //点赞数量
        $recruit['is_praise'] = $praise_logic->get_praise_status(array('uid' => UID, 'objtype' => 2, 'objid' => $data['id']));        //点赞状态
        
        $favorite_logic = D('Content/Favorite', 'Logic');
        $fav_map = array(
            'uid' => UID,
            'objtype' => 2,
            'objid' => $data['id']
        );
        $is_favorite = 0;          //收藏状态
        if($favorite_logic->is_favorite($fav_map)){
            $is_favorite = 1;
        }
        $recruit['is_favorite'] = $is_favorite;
        
        $comment_logic = D('Content/Comment','Logic');
        $comment_count = $comment_logic->get_comment_count(2, $data['id']);         //评论数量
        $comment_list = $comment_logic->get_comment_list(2, $data['id']);
        $comment_list = $comment_list['data'];
        $recruit['comment_count'] = $comment_count;
        $recruit['comment_list'] = $comment_list;
        
        $share = array(
            'share_title' => $recruit['鸟听招聘'],
            'share_content' => $recruit['position_name'],
            'share_img' => 'https://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/logo.png',
            'share_url' => 'http://api.danyangniaoting.com/share?type=2&id='.$data['id'],
            'share_type' => 'recruit'
        );
        $recruit['share'] = $share;
        return array('code' => 200, 'data' => $recruit);
    }
    
    /**
     * 删除招聘信息
     */
    function del_recruit($data){
        if(!$data['id']){
            return array('code' => 707);
        }
        $recruit = M('common_recruit')->where(array('id' => $data['id']))->find();
        if($recruit['uid'] != UID){
            return array('code' => 709);
        }
        if($recruit['deleted'] == 1){
            return array('code' => 708);
        }
        if(M('common_recruit')->where(array('id' => $data['id']))->setField('deleted', 1)){
            return array('code' => 200);
        }else{
            return array('code' => 711);
        }
    }
}
