<?php

namespace Member\Logic;

/**
 * 用户积分逻辑处理
 *
 * @author Kevin
 */
class CreditLogic {

    //private $model;
    protected $rules;

    function __construct() {
        
    }
    
    /**
     * 积分变动
     */
    function log($uid, $type, $data = array()) {
        
        $rules = D('Member/MemberCreditRules');
        $this->rules = $rules->getRules($type);
        
        $data['credit'] = $this->get_credit_value($uid, $type);
        if ($data['credit']) {
            $data['uid'] = $uid;
            $data['crid'] = $this->rules['id'];
            $data['dateline'] = date('Y-m-d H:i:s',NOW_TIME);
            M('member_credit_logs')->data($data)->add();
            
            //删除用户缓存
            //$userinfo = getUserInfo($uid , 2);
            //S(get_cache_key($userinfo['member']['mobile']) , null);
            //S(get_cache_key($uid , 2) , null);
            
            //c_memberfields 加积分
            $where['uid'] = $uid;
            M('member_credits')->where($where)->setInc('credit1', $data['credit']);  //加金币积分

			if($data['credit'] > 0)
				M('member_credits')->where($where)->setInc('credit2', $data['credit']);  //加等级积分

        }
        return $data['credit'];
    }

	/**
     * 取操作的有效积分值
     * @param type $uid
     * @param type $type
     * @return boolean
     */
    function get_credit_value($uid, $type) {
        $rule = $this->rules;
        
        if (!$rule) {
            return false;
        }
        $where = array();
        switch ($rule['cycletype']) {
            case 1:
                $date = date('Y-m-d', NOW_TIME);
                $where = "DATEDIFF(dateline,'{$date}') = 0";
                break;
            case 2:
                $date = date('Y-m-d H', NOW_TIME);
                $where = "DATEDIFF('{$date}',DATE_FORMAT(dateline,'%Y-%m-%d %H')) = 0";
                break;
            case 0:
            default:
        }
        $map['uid']  = $uid;
        $map['crid'] = $rule['id'];
        if ($where)
            $map['_string'] = $where;
        
        $count = M('member_credit_logs')->where($map)->count();
        
        // >=上限次数不增积分
        if($rule['number'] == 0)
            return $rule['cyclecredit']; //不限次数
        else
            return $count >= $rule['number'] ? 0 : $rule['cyclecredit'];
    }


}
