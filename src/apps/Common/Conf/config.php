<?php
/**
 * 通用默认配置
 */
return array(
    'LOAD_EXT_CONFIG' => array('route'),
    'PAGESIZE' => 10,
	
	'PAY'=>array(
		'express_price' =>5, //单位元
		'app_id'        =>'app_mPGiDK5WvfLS8K4i',//pingpp app_id 
		'api_key'       =>'sk_live_OSCS88KeDmLOKanPuLfX1mDG', //pingpp key 
		'success_url'	=>'',//支付成功结果页
		'cancel_url'	=>'',//支付取消页
	),
    //
	///* 模板解析配置 */
    'TMPL_PARSE_STRING' => array(
        //'{CSS_PATH}' =>'/Public/css/',
        //'{IMG_PATH}' => '/Public/imgs/',
        //'{JS_PATH}' =>'/Public/js/',
        //'{FONTS_PATH}' =>'/Public/fonts/',
		'{WEB_URL}' =>'',
    ),

	'WX_CONFIG' => array(
        'token'=>'QHsBkIdC1cNwXcCX', //填写你设定的key
      	'encodingaeskey'=>'TpSHO5sGvkJgm4gbKBviD8gkZOBs5AnQUb3WO5yTTvU', //填写加密用的EncodingAESKey
      	'appid'=>'wxb829f31ecd32bdf5', //填写高级调用功能的app id
      	'appsecret'=>'ba3f44ef3ec443c352f05b99082076f2' //填写高级调用功能的密钥
    ),
    'LOAD_EXT_CONFIG' => 'message',
);
