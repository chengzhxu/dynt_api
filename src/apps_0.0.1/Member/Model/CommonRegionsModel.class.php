<?php

namespace Member\Model;

use Think\Model;

/**
 * Description of CommonRegionsModel
 * 用户地区
 * @author Kevin
 */
class CommonRegionsModel extends Model {
    
    
    /**
     * 根据code获取省市区详情
     * @param unknown $code
     * 查看memcache中是否有，如果没有查看数据库，写入缓存
     * 缓存的键名都以 regions+code为下标
     */
    function getDistrict($code) {
        
        if(!$code) {
            return array('province' => '' , 'city' => '' , 'district' => '','full_district' => '');
        }
        //设置默认地区
        $code = (!empty($code) && strlen($code) == 6) ? $code : '';
        
        $district = S('regions'.$code);
        
        //如果缓存中不存在，写写入缓存
        if(!$district) { 
            $one = substr($code, 0, 2);
            $two = substr($code, 2, 2);
            
            $p = $this->field('name,region_code')->where(array('code' => $one.'0000'))->find();
            $c = $this->field('name')->where(array('code' => $one.$two.'00'))->find();
            $d = $this->field('name')->where(array('code' => $code))->find();
            if($c['name']){
				$full_district = $p['name'] .'•'. $c['name'];
			}else{
				$full_district = $p['name'];
			}
            $district = array(
                            'province' => $p['name'] ? $p['name'] : '',
                            'city' => $c['name'] ? $c['name'] : '',
                            'district' => $d['name'] ? $d['name'] : '',
                            'code' => $code,
							'region_code' => $p['region_code'],
                            //'full_district' => $p['name'] . $c['name'] . $d['name']
							'full_district' => $full_district
                        );
            
            S('regions'.$code , $district);
        }
        
        return $district;
        
    }

}
