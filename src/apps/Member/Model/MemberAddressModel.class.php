<?php

namespace Member\Model;

use Think\Model;

/**
 *
 * 用户收货地址
 * @author Kevin
 */
class MemberAddressModel extends Model {
    
    /**
     * 更新用户收货地址
     * @param unknown $data
     */
    function updateAddress($data) {
        
        $data = delete_empty_array($data);
        
        unset($data['action']);
        
        $data['uid'] = UID;
        
        $where['uid'] = UID;
        $count = $this->where($where)->count();
        if($data['id']) {
			$where['id'] = $data['id'];
            //更新
            if($this->where($where)->save($data)){
				$return['code'] = 200;
			}else{
				$return['code'] = 399;
			}
        } else {
			if($count == 0){
				$data['is_default'] = 1;
			}else{
				$data['is_default'] = 0;
			}
			if($id  = $this->where($where)->add($data)){
				$return['code'] = 200;
				$return['data'] = $id;
			}else{
				$return['code'] = 399;
			}
        }
        
        return $return;
    }
    
    /**
     * 获取用户的收货地址
     * @param unknown $data
     * @param $limit 获取数量 1表示取１条，０表示取所有
     */
    function getAddress($data , $limit = 0) {
        
         $res = $this->where(array('uid' => UID))->order('is_default desc');
         
         if($limit == 1)
             return $res->find();  //取第一条
         else {
             $page = intval($data['page']) ? intval($data['page']) : 1;
             $offset = ($page - 1) * C('PAGESIZE');
             $address = $res->limit($offset , C('PAGESIZE'))->select(); //取所有
             foreach($address as $key => $value) {
                 $address[$key]['full_address'] = $value['province'] . $value['city'] . $value['area'] . $value['street'];
             }
             return $address;
         }
    }

	/**
     * 取消/设置为默认收货地址
     * @param unknown $data
     */
	function setDefaultAddress($data){
		$default = M('MemberAddress')->where( array('id'=>$data['id']) )->getField('is_default');
		//取消设置默认地址
		if($default){
			if(M('MemberAddress')->where( array('id'=>$data['id']) )->setField('is_default',0)){
				return true;
			}else{
				return false;
			}
		}else{
			$defaultId = M('MemberAddress')->where( array('uid'=>UID,'is_default'=>1) )->getField('id');
			if($defaultId){
				
				M('MemberAddress')->where( array('id'=>$defaultId) )->setField('is_default',0);
				if(M('MemberAddress')->where( array('id'=>$data['id']) )->setField('is_default',1)){
					return true;
				}else{
					return false;
				}
			}else{
				if(M('MemberAddress')->where( array('id'=>$data['id']) )->setField('is_default',1)){
					return true;
				}else{
					return false;
				}
			}
			
		}
	}

	/**
     * 删除收货地址
     * @param unknown $data
     */
	 function delAddress($data){
		M('MemberAddress')->where( array('id'=>$data['id'],'uid'=>UID) )->delete();
		return $data = array('code'=>200,'data'=>array());
	 }

	 /**
     * 获取一条收货地址
     * @param unknown $data
     */
	 function getoneaddress($data){
		$one = M('MemberAddress')->where( array('id'=>$data['id'],'uid'=>UID) )->find();
		$one['full_address'] = $one['province'] . $one['city'] . $one['area'] . $one['street'];
		return $data = array('code'=>200,'data'=>$one);
	 }


    
}
