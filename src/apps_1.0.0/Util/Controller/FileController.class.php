<?php

namespace Util\Controller;
use Think\Controller;

/**
 * 上传文件
 *
 * @author Kevin
 */
class FileController extends Controller{
    
    /**
     * 上传图片到oss(多张)
     */
    function upload_oss_img(){
        $oss = D('Common/Oss' , 'Logic');
        
        $success_count = 0;   //上传图片成功个数
        $error_count = 0;     //上传图片失败个数

        for ($i=0; $i<count($_FILES['thumb']['error']); $i++) {
            if ($_FILES['thumb']['error'][$i] == 0) {
                //上传图片
                if($_FILES['thumb']["type"][$i] == 'image/jpeg'){
                    $filename = $this->getMillisecond().'.jpg';
                }elseif($_FILES['thumb']["type"][$i] == 'image/png'){
                       $filename = $this->getMillisecond().'.png';
                }elseif($_FILES['thumb']["type"][$i] == 'image/gif'){
                       $filename = $this->getMillisecond().'.gif';
                }else{
                       $filename = $this->getMillisecond().'.jpg';
                }
                
                $return = $oss->upload_by_multi_part($_FILES['thumb']['tmp_name'][$i], 
                        'upload/' . date('Y-m-d', NOW_TIME),$filename,$_FILES['thumb']['size'][$i]);
                if($return['status'] == 200) {           //上传图片成功
                    $success_count++;
                }else{
                    $error_count++;
                }
            }
        }   
        echo json_encode( array('code'=>200,'message'=>'','data'=> array('success_count'=>$success_count, 'error_count' => $error_count)) );
    }
    
    function getMillisecond() {
            list($t1, $t2) = explode(' ', microtime());
            return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
}
