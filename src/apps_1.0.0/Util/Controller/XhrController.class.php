<?php

namespace Util\Controller;

/**
 * XMLHttpRequest测试
 *
 * @author Kevin
 */
class XhrController extends \Think\Controller{
    function index(){
        $arr=array('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5);  
        $result=json_encode($arr);  
        //echo $_GET['callback'].'("Hello,World!")';  
        //echo $_GET['callback']."($result)";  
        //动态执行回调函数  
        $callback=$_GET['callback'];  
        echo $callback."($result)";  
    }
}
