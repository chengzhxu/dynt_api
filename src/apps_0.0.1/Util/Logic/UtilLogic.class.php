<?php

namespace Util\Logic;

/**
 * Description of UtilLogic
 *
 * @author Kevin
 */
class UtilLogic {
    //获取app启动图
    function get_app_start_img(){
        $map['deleted'] = 0;
        $map['status'] = 1;
        $thumb = M('app_start')->where($map)->order('id desc')->getField('thumb');
        return array('code' => 200, 'data' => array('thumb' => $thumb));
    }
}
