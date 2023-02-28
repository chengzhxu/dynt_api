<?php

namespace Cart\Model;

use Think\Model;

/**
 * Description of PacketsModel
 *
 * @author Kevin
 */
class PacketsModel extends Model {

    protected $trueTableName = 'hjy_red_packets';
    /**
     * 获取用户的红包列表
     * @param $data[amount] 需要支付的总金额
     * @param $data[condition] 条件，新老商品
     * @param $data[gid] 购买的产品ＩＤ
     * @return status=0表示不可使用,status=1表示红包可用
     * @return return 返回的红包列表
     * @return number 返回可用红包的数量
     */
    function getPacketsList($data) {
        
        $number = 0;  //有多少可用红包
        $invalidData = $validData = $firstData = array();
        $first = true;
        $falsenum = 0;
        
        $where = array(
            'a.uid' => UID,
            'a.is_used' => 0,
			'a.over_time' => array('egt' , NOW_TIME),
        );
        if($data['packet_id'])
            $where['a.id'] = $data['packet_id'];
        //获取字段
        $field = 'a.money,a.get_time,a.over_time,b.name,b.min_goods_amount,b.condition,b.obj_type,b.obj_id,a.id';
        $result = $this->alias('a')->join(C('DB_PREFIX') . 'red_packets_type as b on a.type_id = b.id')->field($field)->where($where)->order('a.money desc')->select();
        
        foreach($result as $key => $value) {
            
            $result[$key]['status'] = 1;  //默认红包是有效
            
            //１.验证红包是否己过期
            if($value['over_time'] < NOW_TIME){
                //己过期
                $result[$key]['status'] = 0;
                $falsenum++;
                $result[$key]['message'][] = '红包己过期，使用截止日期'. date('Y-m-d H:i:s' , $value['over_time']);
            }
            
            //２.验证红包金额，满多少有效
            if(!floatval($value['min_goods_amount'])) {
                //未设置最低消费
            } else {
                //设置最低消费
                if($value['min_goods_amount'] > $data['amount']) {
                    //购买的金额不够
                    $result[$key]['status'] = 0;
                    $falsenum++;
                    $result[$key]['message'][] = '消费满'. $value['min_goods_amount'] .'元可使用';
                }
            }
            
            //３.验证类型
            if($value['condition']) {
                //如果红包有使用条件
                $conditions = explode(',' , $value['condition']);
                $status = 0;
                //如果购买的商品有条件设置
                if($data['condition']) {
                    $datacondition = explode(',' , $data['condition']);
                    foreach($datacondition as $v) {
                        if(in_array($v, $conditions)) {
                            $status = 1;
                        }
                    }
                } else 
                    $status = 0;
                
                //不符合使用条件
                if($status == 0) {
                    $result[$key]['status'] = 0;
                    $falsenum++;
                    $result[$key]['message'][] = '在这些类型中可以使用'. get_select('RED_PACKET_CONDITION' , $value['condition']);
                }
            }else{
				 $status = 1;
			}
            
            //４.验证产品品牌
            //obj_type=0表示不限，只有有值说明这个红色类型是对产品或品牌有做限制的
            if($value['obj_type']) {
                $obj_ids = explode(',' , $value['obj_id']);
                unset($where);
                switch ($value['obj_type']) {
                    case 1:
                        //品牌
						if($data['gid']) {
							$where['a.gid'] = array('in' , $data['gid']);
							$res = M('product_goods')->alias('a')->join(C('DB_PREFIX') . 'product_brand as b on a.bid = b.bid')->field('a.bid')->where($where)->select();
						} else 
							$res = array();

                        if($res) {
                            foreach($res as $v) {
                                $gids[] = $v['bid'];
                            }
                        }
						
                        if(!array_intersect($gids, $obj_ids)) {
                            unset($where);
                            $where['bid'] = array('in' , $value['obj_id']);
                            $res = M('product_brand')->field('brand_name')->where($where)->select();
                            if($res) {
                                foreach($res as $v) {
                                    $name .= $v['brand_name'];
                                }
                            }
                            $result[$key]['status'] = 0;
                            $falsenum++;
                            $result[$key]['message'][] = '红包只能在#'. $name .'#品牌中使用';
                        }
                        break;
                    case 2:
                        //产品
                        $gids = explode(',' , $data['gid']);
                        if(!array_intersect($gids , $obj_ids)) {
                            unset($where);
                            $where['gid'] = array('in' , $value['obj_id']);
                            $res = M('product_goods')->field('name')->where($where)->select();
                            if($res) {
                                foreach($res as $v) {
                                    $name .= $v['name'];
                                }
                            }
                            $result[$key]['status'] = 0;
                            $falsenum++;
                            $result[$key]['message'][] = '红包只能在#'. $name .'#产品中使用';
                        }
                        break;
                }
            }
            $result[$key]['gettime']  = date('Y-m-d' , $value['get_time']);
			$result[$key]['overtime']  = date('Y-m-d' , $value['over_time']);
            $result[$key]['falsenum'] = $falsenum;
            $falsenum = 0;
            
            if($result[$key]['status'] == 1) {
                $number++;
                $validData[] = $result[$key];
                if($first == true) {
                    $firstData = $result[$key];
                    $first = false;
                }
            } else {
                $invalidData[] = $result[$key];
            }

        }
        
        $PackData = array_merge($validData , $invalidData);
        
//        if($data['packet_id'] === false)
//            return array('return' => $result , 'number' => $number , 'firstData' => $firstData);
//        else
            return array('return' => $PackData , 'number' => $number , 'firstData' => $firstData);
    }

}
