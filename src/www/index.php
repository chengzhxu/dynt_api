<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用入口文件
// 检测PHP环境
error_reporting(E_ERROR | E_WARNING | E_PARSE);
if (version_compare(PHP_VERSION, '5.3.0', '<'))
    die('require PHP > 5.3.0 !');

if($_GET['version']){
	$apps = 'apps_'.$_GET['version'];
}else{
	$apps = 'apps_0.0.1';//修改入口文件默认值为1.1.0
}
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false

define('APP_DEBUG', true);
define('APP_STATUS', 'prod'); 
//定义应用模式
define('MODE_NAME', 'Home'); 
// 定义应用目录
define('APP_PATH', '../' . $apps . '/');
define('DATA_PATH', '../../data/');
//RUNTIME路径
define('RUNTIME_PATH', DATA_PATH . $apps .'/Runtime/');

// 引入ThinkPHP入口文件
require '../core/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单