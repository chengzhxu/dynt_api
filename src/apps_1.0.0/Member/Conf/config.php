<?php
return array(
	//'配置项'=>'配置值'
	'ALLOW_OTHER_APP_LOGIN' => true,   //一个帐号可以登录所有的APP,true为允许登录，false不允许
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__IMG__' =>  __ROOT__ . '/Public/static/' . MODULE_NAME . '/images',
        '__CSS__' =>  __ROOT__ . '/Public/static/' . MODULE_NAME . '/css',
        '__JS__' =>  __ROOT__ . '/Public/static/' . MODULE_NAME . '/js',
    ),
);