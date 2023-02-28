<?php

return array(
    //'配置项'=>'配置值'
    //数据库配置
    'DB_TYPE' => 'mysqli', // 数据库类型
    'DB_HOST' => '', // 服务器地址
    'DB_NAME' => '', // 数据库名
    'DB_USER' => '', // 用户名
    'DB_PWD' => '', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'dy_', // 数据库表前缀
    'DB_CHARSET' => 'utf8', // 字符集
    //
    'URL_MODEL' => '2', //URL模式    
    'URL_CASE_INSENSITIVE' => true,
    'LOG_RECORD' => true, //是否开启session
    
    //'DATA_CACHE_TYPE' => 'Memcache',
    //'MEMCACHE_HOST' => 'tcp://10.161.214.164:12111',
    
    'API_URL' => '',
    'PAY'=>array(
        'express_price' =>5, //单位元
        'app_id'        =>'app_qTyDqLHe9ifHu1Ci',//pingpp app_id
        // 	    'api_key'       =>'sk_test_GO4KG4mjzPSOijX1SGXPOK08' //pingpp key
        'api_key'       =>'sk_live_OSCS88KeDmLOKanPuLfX1mDG' //pingpp key
    ),
);