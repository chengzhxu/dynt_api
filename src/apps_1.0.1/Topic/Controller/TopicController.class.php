<?php

namespace Topic\Controller;

/**
 * 话题相关
 *
 * @author Kevin
 */
class TopicController extends \Think\Controller{
    /**
     * 新增话题(form表单提交)
     */
    function add_topic(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');
        
//        $oss = D('Common/Oss' , 'Logic');
        $column_id = I('column_id', 0);
        $thumb_type = I('thumb_type', 0);
        $content = I('content', '');
		$uid = I('uid', 0);
		
		//记录logs
		$logs_arr = array(
			'client' => 0,
			'version' => I('version', '0.0.1'),
			'appid' => 1,
			'uid' => $uid,
			'url' => 'Topic',
			'action' => 'add_topic',
			'addtime' => NOW_TIME
		);
		M('common_visitlog')->add($logs_arr);
		
        if($thumb_type > 0){
            if(!$content && count($_FILES) == 0){
                echo json_encode( array('code'=>605,'message'=>'话题内容和图片不能同时为空','data'=> array()) );exit;
            }
        }else{
            if(!$content && count($_FILES['thumb']['error']) == 0){
                echo json_encode( array('code'=>605,'message'=>'话题内容和图片不能同时为空','data'=> array()) );exit;
            }
        }
        
        if(!$uid){
            echo json_encode( array('code'=>437,'message'=>'用户信息验证失败','data'=> array()) );exit;
        }
        
        //保存话题内容
        $topic_arr = array(
            'content' => emoij_to_ubb($content),
            'uid' => $uid,
            'dateline' => NOW_TIME,
            'type' => 0,
            'column_id' => $column_id
        );
        $topic_id = M('common_topic')->add($topic_arr);
        if(!$topic_id){
            echo json_encode( array('code'=>605,'message'=>'新增话题失败','data'=> array()) );exit;
        }
        
        $success_count = 0;   //上传图片成功个数
        $error_count = 0;     //上传图片失败个数
        
        $upload_path = 'uploads/topic/';           //图片保存路径      
        
        if($thumb_type > 0){      //android图片类型 
            for($i = 0; $i < count($_FILES); $i++){
                if($_FILES['thumb' . $i]['tmp_name'] && $_FILES['thumb' . $i]['error'] == 0){
                    if($_FILES['thumb' . $i]["type"] == 'image/jpeg'){
                        $filename = getMillisecond().'.jpg';
                    }elseif($_FILES['thumb' . $i]["type"] == 'image/png'){
                           $filename = getMillisecond().'.png';
                    }elseif($_FILES['thumb' . $i]["type"] == 'image/gif'){
                           $filename = getMillisecond().'.gif';
                    }else{
                           $filename = getMillisecond().'.jpg';
                    }
                    
                    if(move_uploaded_file($_FILES['thumb' . $i]['tmp_name'], $upload_path . $filename)){
//                        if($_FILES['thumb' . $i]['size'] > 1*1024*1024){      //大于1M压缩
//                            image_png_size_add('http://api.danyangniaoting.com/' . $upload_path . $filename, $upload_path . $filename, 500, 'save');    //生成压缩图
//                            $topic_detail= array('topic_id' => $topic_id, 'location' => 'http://api.danyangniaoting.com/' . $upload_path . $filename);
//                        }else{
                            $topic_detail= array('topic_id' => $topic_id, 'location' => 'http://api.danyangniaoting.com/' . $upload_path . $filename);
//                        }
                        M('common_topic_detail')->add($topic_detail);
                        $success_count++;
                    }else{
                        $error_count++;
                    }
                }
            }
        }else{      //IOS图片类型
            for ($i=0; $i<count($_FILES['thumb']['error']); $i++) {
                if ($_FILES['thumb']['error'][$i] == 0) {
                        //上传图片
                    if($_FILES['thumb']["type"][$i] == 'image/jpeg'){
                        $filename = getMillisecond().'.jpg';
                    }elseif($_FILES['thumb']["type"][$i] == 'image/png'){
                           $filename = getMillisecond().'.png';
                    }elseif($_FILES['thumb']["type"][$i] == 'image/gif'){
                           $filename = getMillisecond().'.gif';
                    }else{
                           $filename = getMillisecond().'.jpg';
                    }

    //                    $return = $oss->upload_by_multi_part($_FILES['thumb']['tmp_name'][$i], 
    //                            'upload/' . date('Y-m-d', NOW_TIME),$filename,$_FILES['thumb']['size'][$i]);
    //                    ob_start();
    //                    $data = file_get_contents($_FILES['thumb']['tmp_name'][$i]);
    //                    $res = imagecreatefromstring($data);
    //                    imagejpeg($res , NULL , 70);
    //                    $imgdata = ob_get_clean();
    //                    if($res && $imgdata){
    //                        $return = $oss->upload_by_content($imgdata , $filename);
    //                    }else{
    //                        $return['status'] = -1;
    //                    }
    //                    if($return['status'] == 200) {           //上传图片成功
    //                        $topic_detail= array('topic_id' => $topic_id, 'location' => $return['url']);
    //                        M('common_topic_detail')->add($topic_detail);
    //                        $success_count++;
    //                    }else{
    //                        $error_count++;
    //                    }

                    if(move_uploaded_file($_FILES['thumb']['tmp_name'][$i], $upload_path . $filename)){
//                        if($_FILES['thumb']['size'][$i] > 1*1024*1024){      //大于1M压缩
//                            image_png_size_add('http://api.danyangniaoting.com/' . $upload_path . $filename, $upload_path . $filename, 500, 'save');    //生成压缩图
//                            $topic_detail= array('topic_id' => $topic_id, 'location' => 'http://api.danyangniaoting.com/' . $upload_path . $filename);
//                        }else{
                            $topic_detail= array('topic_id' => $topic_id, 'location' => 'http://api.danyangniaoting.com/' . $upload_path . $filename);
//                        }
                        M('common_topic_detail')->add($topic_detail);
                        $success_count++;
                    }else{
                        $error_count++;
                    }
                }
            }
        }
        
        

        echo json_encode( array('code'=>200,'message'=>'','data'=> array('success_count'=>$success_count, 'error_count' => $error_count)) );
    }
    
    
    /**
     * 同步话题图片从服务器到阿里云oss  (每天0点执行)
     */
    function update_thumb_to_oss(){
        $page = I('get.page',1,'intval');
        $max_id = S('topic_thumb_max_id');
        if(!$max_id){
            $max_id = 389;
        }
        $page_size = 20;
        $offset = ($page - 1) * $page_size;
        $map['id'] = array('gt', $max_id);
        $thumb_list = M('common_topic_detail')->where($map)->limit($offset,$page_size)->order('id')->select();
        if(count($thumb_list) > 0){
            $oss = D('Common/Oss' , 'Logic');
            foreach ($thumb_list as $key => $value) {
                $filearr = explode('/',$value['location']);
                $index = count($filearr) - 1;
                $filename = $filearr[$index];
                $filepath = 'uploads/topic/' . $filename;
                if(file_exists($filepath)){
                    ob_start();
                    $data = file_get_contents($filepath);
                    $res = imagecreatefromstring($data);
                    imagejpeg($res , NULL , 70);
                    $imgdata = ob_get_clean();
                    if($res && $imgdata){
                        $return = $oss->upload_by_content($imgdata , $filename);
                    }else{
                        $return['status'] = -1;
                    }
                    if($return['status'] == 200) {           //上传图片成功，更改数据库图片地址
                        if(M('common_topic_detail')->where(array('id' => $value['id']))->setField('location', $return['url'])){
                            //更改地址成功，移除服务器图片
                            unlink($filepath);
                            $max_id = $value['id'];
                        }
                    }
                }
            }
            S('topic_thumb_max_id', $max_id);
            $page = $page+1;
            $url = "http://api.danyangniaoting.com/topic/topic/update_thumb_to_oss?page=$page";
            echo "<script>window.location.href = '$url'; </script>";exit;
        }else{
            echo '已更新完成';
            exit;
        }
    }
    
    
    function test(){
        $ja = array('aaa', 'bbb');
        $a = json_encode($ja);
        print_r($a);
    }
}
