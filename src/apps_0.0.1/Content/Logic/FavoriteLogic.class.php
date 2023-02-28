<?php

namespace Content\Logic;

/**
 * 收藏相关
 *
 * @author kevin
 */
class FavoriteLogic {
    /**
     * 收藏or取消收藏
     */
    function favorite($data){
        if(!$data['objtype'] || !$data['objid']){
            $return['code'] = 302;
            $return['message'] = '';
            return $return;
        }
        $favorite = M('common_favorite')->where(array('objtype'=> $data['objtype'], 'objid' => $data['objid']))->find();
        $type = 'favorite';
        $is_favorite = 0;
        if(!$favorite){            //收藏
            $uid = $data['uid'];
            if(!$uid){
                $table = getTable($data['objtype']);
                $uid = M($table)->where(array('id' => $data['objid']))->getField('uid');
            }
            $favorite_arr = array(
                'uid' => UID,
                'fuid' => $uid ? $uid : 0,
                'objtype'=> $data['objtype'],
                'objid' => $data['objid'],
                'dateline' => NOW_TIME
            );
            $favorite_id = M('common_favorite')->add($favorite_arr);
            if(!$favorite_id){
                $return['code'] = 303;
                $return['message'] = '';
                return $return;
            }
            $is_favorite = 1;
        }else{          //取消收藏
            M('common_favorite')->where(array('objtype'=> $data['objtype'], 'objid' => $data['objid']))->delete();
            $type = 'cancel_favorite';
        }
        $return['code'] = 200;
        $return['data'] = array('type' => $type, 'is_favorite' => $is_favorite);
        return $return;
    }
    
    /**
     * 判断是否收藏
     */
    function is_favorite($data){
        if($data){
            if(M('common_favorite')->where($data)->find()){
                return true;
            }
        }
    }
    
    /**
     * 获取我的收藏列表
     */
    function my_favorite($data){
        $page = intval($data['page']) ? intval($data['page']) : 1;
        $page_size = 10;
        $offset = ($page - 1) * $page_size;
        
        $map['uid'] = UID;
        $field = 'objtype,objid';
        $list = M('common_favorite')->where($map)->field($field)->order('dateline desc')->limit($offset , $page_size)->select();
        
        foreach ($list as $key => $value) {
            $data = $this->get_favorite_data($value['objtype'], $value['objid']);
            $list[$key]['detail'] = $data;
        }
        return array('code' => 200, 'data' => $list);
    }
    
    /**
     * 根据类型获取收藏对象
     */
    function get_favorite_data($objtype, $objid = 0){
        $data = array();
        switch ($objtype) {
            case 1:     //话题
                $topic = M('common_topic')->where(array('id' => $objid))->field('id, content, uid, dateline, type as topic_type, display_order, deleted')->find();
                
                $data = fixed_topic($topic);
                break;

            case 2:     //招聘
                $field = 'id, uid, position_name, company_name, address_detail, mobile, realname, salary, address, education, experience, age, gender, duty, dateline, display_order, deleted';
                $recruit = M('common_recruit')->where(array('id' => $objid))->field($field)->find();
                
                $data = fixed_recruit($recruit);
                break;
            
            case 3:   //求职
                $field = 'id, uid, position_name, mobile, realname, job_status, salary, address, education, experience, age, gender, introduce, dateline, display_order, deleted';
                $job = M('common_job')->where(array('id' => $objid))->field($field)->find();
                
                $data = fixed_job($job);
                break;
        }
        return $data;
    }
}
