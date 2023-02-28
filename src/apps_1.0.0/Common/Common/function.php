<?php

/**
 * @author kevin
 */

/**
 * 只有PHP以apache服务器的模块(module)方式执行时 getallheaders这个系统自带方法
 * 如果是nginx的话是不存在 getallheaders 这个方法的
 * 这个是解决除apache以外的服务器来获取所有 HTTP 变量值，
 */
if (!function_exists('getallheaders')) {

    function getallheaders() {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:Authorization');
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
//        if(!$headers){
//            $headers = apache_request_headers();
//        }
        return $headers;
    }

}

/**
 * 验证手机号合法性
 * @param type $mobile
 * @return boolean
 */
function validate_mobile($mobile) {
    $search = '/1[0-9]{1}\d{9}$/';
    if (preg_match($search, $mobile)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 根据后台配置的枚举类型，分割成数组
 * @param unknown $data
 * @param $returnvalue -1的话表示返回一个数据，其它值表示取这个值对应的文字
 */
function get_select($name = '' , $returnvalue = -1) {
    
    $return = array();
    $data = D('SysConfig')->where(array('status' => 1, 'name' => $name))->field('id,name,title,extra,value,remark,type')->order('sort')->find();
    
    if($data) {
        if($data['extra']) {
            $res = explode("\n" , $data['extra']);
            foreach($res as $value) {
                $datas = explode(':' , $value);
                
                if($returnvalue > -1) {
                    if($datas[0] == $returnvalue)
                        return preg_replace("/\r/","",$datas[1]);
                }
                $return[$datas[0]] = trim($datas[1]);
            }
        }
    }
    
    return $return;
}

/**
 * 根据后台配置的枚举类型，分割成数组(键值)
 * @param unknown $data
 * @param $returnvalue -1的话表示返回一个数据，其它值表示取这个值对应的文字
 */
function get_select_double($name = '') {
    
    $return = array();
    $data = D('SysConfig')->where(array('status' => 1, 'name' => $name))->field('id,name,title,extra,value,remark,type')->order('sort')->find();
    
    if($data) {
        if($data['extra']) {
            $res = explode("\n" , $data['extra']);
            foreach($res as $value) {
                $datas = explode(':' , $value);
                
                if($returnvalue > -1) {
                    if($datas[0] == $returnvalue)
                        return preg_replace("/\r/","",$datas[1]);
                }
                $new_arr = array('id' => $datas[0], 'value' => trim($datas[1]));
                array_push($return, $new_arr);
            }
        }
    }
    
    return $return;
}

/**
 * 获取对象表名
 */
function getTable($objtype){
    $table = '';
    switch ($objtype) {
        case 1:        //话题
            $table = 'common_topic';
            break;
        case 2:        //招聘
            $table = 'common_recruit';
            break;
        case 3:        //求职
            $table = 'common_job';
            break;
    }
    return $table;
}

/**
 * 返回用户年龄
 * unixtime
 */
function getUserAge($birthday) {
    $birthday = (is_string($birthday)) ? strtotime($birthday) : $birthday;
    if ($birthday > 0) {
        $year = date('Y', $birthday);
        if (($month = (date('m') - date('m', $birthday))) < 0) {
            $year++;
        } else if ($month == 0 && date('d') - date('d', $birthday) < 0) {
            $year++;
        }
        return date('Y') - $year;
    } else
        return 0;  //默认25岁
}

/**
 * 获取用户信息方法
 * 公用方法，方便直接调用
 * $username 用户名或手机
 * $type是根据用户名获取还是UID获取，type=1 用户名  type=2UID
 */
function getUserInfo($username, $type = 1) {

    return D('Member/Member')->getUserInfo($username, $type);
}

/**
 * 返回默认头像
 */
function getDefaultHeadimg($uid){
    $gender = 1;
    if($uid){
        $gender = M('member_account')->where(array('uid' => $uid))->getField('gender');
    }
    $b_headimg_arr = array(
        '0' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/man1.png',
        '1' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/man2.png',
        '2' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/man3.png',
        '3' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/man4.png',
        '4' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/man5.png',
        '5' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/man6.png'
    );
    $g_headimg_arr = array(
        '0' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/women1.png',
        '1' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/women2.png',
        '2' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/women3.png',
        '3' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/women4.png',
        '4' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/women5.png',
        '5' => 'http://niaoting-bucket.oss-cn-shanghai.aliyuncs.com/default_headimg/women6.png'
    );
    $rand = mt_rand(0, 5);
    if($gender == 0){    //女
        $headimg = $g_headimg_arr[$rand];
    }else{
        $headimg = $b_headimg_arr[$rand];
    }
    if($uid){
        $old_headimg = M('member_account')->where(array('uid' => $uid))->getField('headimg');
        if(!$old_headimg){
            if(M('member_account')->where(array('uid' => $uid))->setField('headimg', $headimg)){
                $mobile = M('member_account')->where(array('uid' => $uid))->getField('mobile');
                S(get_cache_key($uid , 2) , null);
                S(get_cache_key($mobile , 1) , null);
            }
        }else{
            $headimg = $old_headimg;
            S(get_cache_key($uid , 2) , null);
            S(get_cache_key($mobile , 1) , null);
        }
        
    }
    return $headimg;
}


/**
 * 评论递归
 * @param intval $id			评论ID
 */
 function getReply($id, $new_reply = array()){
     $Date = new \Org\Util\Date();
	//查看是否有回复
	$reply = M('common_comment')->where(array('deleted' => 0 ,'parent_id' => $id))->order('id')->select();
        
	if($reply) {
            foreach($reply as $v) {
                $userinfo = getUserInfo($v['from_uid'] , 2);

                $v['from_headimg'] = !empty($userinfo['headimg']) ? $userinfo['headimg'] : getDefaultHeadimg($v['from_uid']);
                $v['from_nick']= !empty($userinfo['nickname']) ? $userinfo['nickname'] : '路人甲';

                $u = getUserInfo($v['to_uid'] , 2);
                $v['to_headimg'] = !empty($u['headimg']) ? $u['headimg'] : getDefaultHeadimg($v['to_uid']);
                $v['to_nick'] = !empty($u['nickname']) ? $u['nickname'] : '路人丙';
                $v['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $v['dateline']));
                $v['content'] = ubb_to_emoij($v['content']);
                
                array_push($new_reply, $v);
                $new_reply = getReply($v['id'], $new_reply);
            }
	}
        
	return $new_reply;
 }
 
 /**
 * 
 * @param number $type
 * @param $type=1 用户名/手机，$type=2 UID获取
 */
function get_cache_key($username, $type = 1) {

//    $config = C('ALLOW_OTHER_APP_LOGIN');
//    $appid = APPID;

    if ($type == 1) {
//        if (!$config)
//            $key = 'member' . $username . $appid; //一个帐号不可以登录所有的APP,key值就是手机号+appid
//        else
            $key = 'member' . $username;  //一个帐号可以登录所有的APP
    } else {
        $key = 'uid' . $username;
    }

    return $key;
}


/**
 * 获取丹阳所有地区(配置)
 */
function getAllAddress($type = 0){
    $address = get_select_double('WORK_ADDRESS');
    if($type == 1){               //判断第一个元素是否是不限，并且去除
        if($address[0]['value'] == '不限'){
            array_shift($address);
        }
    }
    return $address;
}

/**
 * 保存消息
 */
function addMessage($data){
    if($data){
        M('common_message')->add($data);
    }
}

/**
 * 获取求职人员年龄选择
 */
function getJobAge(){
    $age_list = array();
    for($i = 18; $i < 62; $i++){
        $arr = array('id' => $i, 'value' => $i);
        array_push($age_list, $arr);
    }
    return $age_list;
}

/**
 * 判断字符串是否经过编码
 */
function is_base64($str){
    if($str == base64_encode(base64_decode($str))){
        return true;
    }else{
        return false;
    }
}

/**
 * 获取图片比例
 */
function getImageRatio($img_path){
    $img_info = getimagesize($img_path);
    return sprintf("%.2f", $img_info[0] / $img_info[1]);
}

function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}


/**
 * emoij图片转成ubb
 * @param unknown $content
 * @return string|mixed
 */
function emoij_to_ubb($content) {
    
    if(!$content)
        return '';
    
    $tmpStr = json_encode($content); //暴露出unicode
    $tmpStr = preg_replace("#(\\\ud[0-9a-f]{3})#ie","addslashes('\\1')",$tmpStr); //将emoji的unicode留下，其他不动
    $text = json_decode($tmpStr);
    return $text;
}
/**
 * ubb 转成 emoij图片
 * @param unknown $content
 * @return string|mixed
 */
function ubb_to_emoij($content) {
    
    if(!$content)
        return '';

	if(preg_match('/\\\\ud/' , $content)) {
		//echo $content;

		preg_match_all("#(\\\ud[0-9a-f]{3})#ie" , $content , $matchs);
		
		$par = '';
		if($matchs[0]) {
			foreach($matchs[0] as $v) {
                            if(!strstr($par, $v)){
                                if($par != '' && strstr($v, 'ud83')){
                                    $replace = "\"" . $par ."\"";
                                    $content = str_replace($par , json_decode($replace) , $content);
                                    $par = '';
                                }
                                $par .= $v;
                            }else{
                                $replace = "\"" . $par ."\"";
                                $content = str_replace($par , json_decode($replace) , $content);
                                $par = '';
                                $par .= $v;
                            }
			}
		}

		$replace = "\"" . $par ."\"";
		
		$text = str_replace($par , json_decode($replace) , $content);

	} else {
		$text = $content;
	}
    
    return $text;
}


/** 
* desription 压缩图片 
* @param sting $imgsrc 图片路径 
* @param string $imgdst 压缩后保存路径 
*/
function image_png_size_add($imgsrc,$imgdst, $new_width = 500, $t = 'view'){ 
  list($width,$height,$type)=getimagesize($imgsrc); 
  //$new_width = ($width>600?600:$width)*0.7; 
  //$new_height =($height>600?600:$height)*0.7; 
  $ratio = getImageRatio($imgsrc);
  $new_height = $new_width / $ratio;
  switch($type){ 
    case 1: 
      $giftype=check_gifcartoon($imgsrc); 
      if($giftype){ 
          if($t == 'view'){
              header('Content-Type:image/gif'); 
          }
        $image_wp=imagecreatetruecolor($new_width, $new_height); 
        $image = imagecreatefromgif($imgsrc); 
        imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($image_wp, $imgdst); 
        imagedestroy($image_wp); 
      } 
      break; 
    case 2: 
        if($t == 'view'){
              header('Content-Type:image/jpeg'); 
          }
      $image_wp=imagecreatetruecolor($new_width, $new_height); 
      $image = imagecreatefromjpeg($imgsrc); 
      imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
      imagejpeg($image_wp, $imgdst); 
      imagedestroy($image_wp); 
      break; 
    case 3: 
        if($t == 'view'){
            header('Content-Type:image/png'); 
        }
      $image_wp=imagecreatetruecolor($new_width, $new_height); 
      $image = imagecreatefrompng($imgsrc); 
      imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
      imagejpeg($image_wp, $imgdst); 
      break; 
  } 
  return true;
} 
/** 
* desription 判断是否gif动画 
* @param sting $image_file图片路径 
* @return boolean t 是 f 否 
*/
function check_gifcartoon($image_file){ 
  $fp = fopen($image_file,'rb'); 
  $image_head = fread($fp,1024); 
  fclose($fp); 
  return preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)?false:true; 
} 


/**
 * 设置图片尺寸
 * @param $url		图片地址
 * @param $width	图片宽度
 * @param $height	图片高度
 */
function imageResizer($url, $width, $height) {
	  $imgType = pathinfo($url,PATHINFO_EXTENSION );
	  if($imgType == 'png'){
		 header('Content-Type: image/png');
          }else if($imgType == 'gif'){
              header('Content-Type: image/gif');
          }else{
		 header('Content-Type: image/jpeg');
	  }
	 
	  list($width_orig, $height_orig) = getimagesize($url);
	  $ratio_orig = $width_orig/$height_orig;
	  if ($width/$height > $ratio_orig) {
	   $width = $height*$ratio_orig;
	  } else {
	   $height = $width/$ratio_orig;
	  }
	  // This resamples the image
	  $image_p = imagecreatetruecolor($width, $height);
	  
	  if($imgType == 'png'){
		$image = imagecreatefromjpeg($url);
		//dump($image);exit;
          }else if($imgType == 'gif'){
              $image = imagecreatefromgif($url);
          }else{
		 $image = imagecreatefromjpeg($url);
	  }
	  //$image = imagecreatefromjpeg($url);
	  if(!$image){
		$image = imagecreatefrompng($url);
	  }
	  
	  
	  imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	  // Output the image
	  if($imgType == 'png'){
		imagepng($image_p);
	  }else if($imgType == 'gif'){
                imagegif($image_p);
          }else{
		imagejpeg($image_p);
	  }
	  
 }
 
 
  function pictumb($url, $width, $height){
        $dstFile = $url;//保留名字
        //header('Content-Type: image/jpeg');
        // 获取新的尺寸
        list($width, $height) = getimagesize($url);
        if ($width>600){
            $new_width = $width;
            $new_height = $height;
        }else{
            $new_width =$width;
            $new_height = $height;
        }
            // 重新取样
            $image_p = imagecreatetruecolor($new_width, $new_height);
            //设置颜色
            $color=imagecolorallocate($image_p,255,255,255); 
            imagecolortransparent($image_p,$color); 
            imagefill($image_p,0,0,$color); 
            //获取格式
            $format=substr($url,strrpos($url, '.'));
            switch ($format) {
                case '.png':
                    $image=imagecreatefrompng($url);
                    break;
                case '.jpeg':
                    $image=imagecreatefromjpeg($url);
                    break;
                case '.bmp':
                    $image=imagecreatefromwbmp($url);
                    break;
                case '.gif':
                    $image=imagecreatefromgif($url);
                    break;
                default:
                     $image=imagecreatefromjpeg($url);
                    break;
            }
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            chmod($url,0777);//unlink函数要求对删除的图片有777的权限
            unlink($url);//先删除图片，在进行保存
            // 输出
            switch ($format) {
                case '.png':
                    imagepng($image_p,$dstFile);
                    break;
                case '.jpeg':
                    imagejpeg($image_p,$dstFile, 1);
                    break;
                case '.bmp':
                    imagewbmp($image_p,$dstFile);
                    break;
                case '.gif':
                    imagegif( $image_p,$dstFile);
                    break;
                default:
                     imagejpeg($image_p,$dstFile,1);
                    break;
            }
    }
    
    //图片压缩  
function ImageCondens($filepase, $width, $height){  
    list($new_width,$new_height,$imgtype)=getimagesize($filepase);  
//    if($new_width>550){//550为自定义宽度  
//        $scaling=$new_width/550;//缩放比例  
//        $picwidth=($new_width/$scaling);  
//        $picheight=($new_height/$scaling);  
//    }else{  
//        $picwidth=$new_width;  
//        $picheight=$new_height;  
//    }  
    $picwidth = $width;  
    $picheight = $height; 
    switch ($imgtype){  
        case 1:  
         $fp=fopen($filepase,'rb');  
         $image_head = fread($fp,1024);  
            fclose($fp);  
         if(preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)){//屏蔽gif动画  
             //echo "<script>alert('系统不支持GIF动画图片');</script>";  
             header('Content-Type:image/gif');  
                 $img_wp=imagecreatetruecolor($picwidth,$picheight);  
                 $img=imagecreatefromgif($filepase);  
                 imagecopyresampled($img_wp,$img,0,0,0,0,$picwidth,$picheight,$new_width,$new_height);  
                 imagejpeg($img_wp,null,100);  
                 imagedestroy($img_wp);
         }else{  
             if($image_head){  
                 header('Content-Type:image/gif');  
                 $img_wp=imagecreatetruecolor($picwidth,$picheight);  
                 $img=imagecreatefromgif($filepase);  
                 imagecopyresampled($img_wp,$img,0,0,0,0,$picwidth,$picheight,$new_width,$new_height);  
                 imagejpeg($img_wp,null,100);  
                 imagedestroy($img_wp);  
             }  
         }  
         break;  
        case 2:  
            header('Content-Type:image/jpeg');  
            $img_wp=imagecreatetruecolor($picwidth,$picheight);  
            $img = imagecreatefromjpeg($filepase);  
            imagecopyresampled($img_wp,$img,0,0,0,0,$picwidth,$picheight,$new_width,$new_height);  
            imagejpeg($img_wp,null,100);  
            imagedestroy($img_wp);  
            break;  
        case 3:  
            header('Content-Type:image/png');  
            $img_wp=imagecreatetruecolor($picwidth,$picheight);  
            $img = imagecreatefrompng($filepase);  
            imagecopyresampled($img_wp,$img,0,0,0,0,$picwidth,$picheight,$new_width,$new_height);  
            imagejpeg($img_wp,null,100);  
            imagedestroy($img_wp);  
            break;  
    }  
  
}  
 
 
 /**
  * 定义话题列表通用输出结构
  */
 function fixed_topic($topic){
    $Date = new \Org\Util\Date();
    $topic['topic_id'] = $topic['id'];
    $topic['objtype'] = 1;
    $topic['objid'] = $topic['id'];
    $topic['content'] = ubb_to_emoij($topic['content']);
    $detail = M('common_topic_detail')->where(array('topic_id' => $topic['id']))->field('id,location')->select();
    foreach ($detail as $k => $val) {
//        $img_size = filesize($val['location']);
//        $detail[$k]['file_size'] = $img_size;
        $img_info = getimagesize($val['location']);
        $ratio = sprintf("%.2f", $img_info[0] / $img_info[1]);
        $detail[$k]['ratio'] = $ratio;
        $detail[$k]['small_thumb'] = $val['location'];
//        if(true){    //图片大于100k压缩
            $thumb_width = $img_info[0];
            if($img_info[0] > 180){
                $thumb_width = 180;
                
                $thumb_height = round($thumb_width / $ratio);
    //                if(!file_exists('uploads/topic/topic_thumb_' . $thumb_width . '_' .$val['id'].'.jpg')){
    //                    image_png_size_add($val['location'], 'uploads/topic/topic_thumb_'.$thumb_width.'_'.$val['id'].'.jpg', $thumb_width);    //生成压缩图
    //                }
    //                $detail[$k]['small_thumb'] = 'http://api.danyangniaoting.com/uploads/topic/topic_thumb_'.$thumb_width.'_'.$val['id'].'.jpg';

                $resimgurl = str_replace('http://','',$val['location']);
                $resimgurl = str_replace('/','_',$resimgurl);
                $detail[$k]['small_thumb'] = 'http://api.danyangniaoting.com/util/image/Resizerimg/w/'.$thumb_width.'/h/'.$thumb_height.'/url/'.$resimgurl;
            }
            
            
//        }else{
//            $detail[$k]['small_thumb'] = $val['location'];
//        }
        $imgType = pathinfo($val['location'],PATHINFO_EXTENSION );
        $detail[$k]['img_type'] = $imgType;
            $fp=fopen($val['location'],'rb');  
         $image_head = fread($fp,1024);  
            fclose($fp);  
         if(preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)){
             $detail[$k]['img_type'] = 'gif';
         }
        
    }
    $topic['detail_arr'] = $detail;

    $userinfo = getUserInfo($topic['uid'], 2);
    $topic['nickname'] = $userinfo['nickname'] ? $userinfo['nickname'] : '路人甲';
    $topic['headimg'] = $userinfo['headimg'] ? $userinfo['headimg'] : getDefaultHeadimg($topic['uid']);
    $topic['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $topic['dateline']));


    $favorite_logic = D('Content/Favorite', 'Logic');
    $fav_map = array(
        'uid' => UID,
        'objtype' => 1,
        'objid' => $topic['id']
    );
    $is_favorite = 0;          //收藏状态
    if($favorite_logic->is_favorite($fav_map)){
        $is_favorite = 1;
    }
    $topic['is_favorite'] = $is_favorite;

    $is_follow = 0;           //是否关注当前用户
    if(M('sns_follow')->where(array('uid' => UID, 'fid' => $topic['uid']))->find()){
        $is_follow = 1;
    }
    $topic['is_follow'] = $is_follow;
    return $topic;
 }
 
 /**
  * 定义招聘列表通用输出结构
  */
 function fixed_recruit($recruit){
     $Date = new \Org\Util\Date();
        $recruit['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $recruit['dateline']));
        $recruit['objtype'] = 2;
        $recruit['objid'] = $recruit['id'];
        $recruit['salary'] = get_select('WORK_SALARY', $recruit['salary']);   //薪资范围
        $recruit['education'] = get_select('WORK_EDUCATION', $recruit['education']);   //学历
        $recruit['experience'] = get_select('WORK_EXPERIENCE', $recruit['experience']);   //经验
        $recruit['age'] = get_select('WORK_AGE', $recruit['age']);   //年龄
        $recruit['gender'] = get_select('WORK_GENDER', $recruit['gender']);   //性别
        $recruit['address'] = get_select('WORK_ADDRESS', $recruit['address']);   //地区
        $recruit['duty'] = ubb_to_emoij($recruit['duty']);


        $favorite_logic = D('Content/Favorite', 'Logic');
        $fav_map = array(
            'uid' => UID,
            'objtype' => 2,
            'objid' => $recruit['id']
        );
        $is_favorite = 0;          //收藏状态
        if($favorite_logic->is_favorite($fav_map)){
            $is_favorite = 1;
        }
        $recruit['is_favorite'] = $is_favorite;

        $is_follow = 0;           //是否关注当前用户
        if(M('sns_follow')->where(array('uid' => UID, 'fid' => $recruit['uid']))->find()){
            $is_follow = 1;
        }
        $recruit['is_follow'] = $is_follow;
        
        return $recruit;
 }
 
  /**
  * 定义求职列表通用输出结构
  */
 function fixed_job($job){
     $Date = new \Org\Util\Date();
     $job['dateline'] = $Date->timeDiff(date('Y-m-d H:i:s', $job['dateline']));
        $job['objtype'] = 3;
        $job['objid'] = $job['id'];
        $job['salary'] = get_select('WORK_SALARY', $job['salary']);   //薪资范围
        $job['education'] = get_select('WORK_EDUCATION', $job['education']);   //学历
        $job['experience'] = get_select('WORK_EXPERIENCE', $job['experience']);   //经验
        $job['job_status'] = get_select('WORK_STATUS', $job['job_status']);   //工作状态
        $job['age'] = $job['age'];   //年龄
        $job['gender'] = get_select('WORK_GENDER', $job['gender']);   //性别
        $job['address'] = get_select('WORK_ADDRESS', $job['address']);   //地区
        $job['introduce'] = ubb_to_emoij($job['introduce']);

        $favorite_logic = D('Content/Favorite', 'Logic');
        $fav_map = array(
            'uid' => UID,
            'objtype' => 3,
            'objid' => $job['id']
        );
        $is_favorite = 0;          //收藏状态
        if($favorite_logic->is_favorite($fav_map)){
            $is_favorite = 1;
        }
        $job['is_favorite'] = $is_favorite;

        $is_follow = 0;           //是否关注当前用户
        if(M('sns_follow')->where(array('uid' => UID, 'fid' => $job['uid']))->find()){
            $is_follow = 1;
        }
        $job['is_follow'] = $is_follow;
        
        return $job;
 }