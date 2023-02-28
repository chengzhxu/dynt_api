<?php

namespace Member\Model;

use Think\Model;

/**
 * 积分规则
 *
 * @author Kevin
 */
class MemberCreditRulesModel extends Model {

    /**
     * 根据积分类型，取出规则
     * 先用 S方法 从缓存中取出，如果没有的话，从数据库中查询，查出来后记录进缓存
     * @return array
     */
    function getRules($action) {
        
        if(!$action)
            return false;
        
        $res = array();
        $rules = S('rules'.$action);
        
        //如果不存在，则从数据库中查询，再记录进缓存
        if(!$rules) {
            
            $where['action'] = $action;
            $rules = $this->where($where)->find();
            if($rules) {
                //记录缓存
                S('rules'.$action , $rules);
            }
        } 
        return $rules;
    }
    

}
