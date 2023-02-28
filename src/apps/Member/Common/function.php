<?php

/**
 * 第三方用户登录/绑定时数据字段
 * @param unknown $data
 * @return unknown
 */
function bindThirduserData($data) {
    
    switch ($data['type']) {
        case 'qq':
            $third['table'] = 'member_oauth_qq';
            $third['openid'] = $data['openid'];
            $third['nickname'] = $data['screen_name'];
            $third['gender'] = $data['gender'];
            $third['headimg'] = $data['profile_image_url'];
            break;
        case 'weixin':
            $third['table'] = 'member_oauth_weixin';
            $third['openid'] = $data['openid'];
            $third['nickname'] = $data['screen_name'];
            $third['gender'] = $data['gender'];
            $third['headimg'] = $data['profile_image_url'];
            break;
        case 'weibo':
            $third['table'] = 'member_oauth_weibo';
            $third['openid'] = $data['uid'];
            $third['nickname'] = $data['screen_name'];
            $third['gender'] = $data['gender'];
            $third['headimg'] = $data['profile_image_url'];
            break;
        default:
            break;
    }
    
    return $third;
}