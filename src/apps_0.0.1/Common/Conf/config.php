<?php
/**
 * 通用默认配置
 */
return array(
    'ARTICLE_IMG_PATH' => '', //文章图片的路径前缀
    'LOAD_EXT_CONFIG' => array('route'),
    'ALLOW_OTHER_APP' => 1, //是否允许其它APP用户登录
    'OSS_BUCKET' => 'niaoting-bucket', //OSS BUCKET
    'PAGESIZE' => 10,
    'LOAD_EXT_CONFIG' => 'user,message,level',
    'ALLOW_ACTION'    => array('send' , 'reply' , 'praise'),
    'SHORT_MSG_TYPE' => array('register', 'resetpwd', 'binding')    //短信类型
);
