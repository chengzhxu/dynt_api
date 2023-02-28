<?php

namespace Common\Logic;

/**
 * 附件上传到阿里云OSS
 *
 * @author Kevin
 */
class OssLogic {

    private $oss_sdk_service;
    private $folder;

    /**
     * 
     */
    function __construct() {
        
        Vendor('Oss.ALYOSS#class');
        $this->oss_sdk_service = new \ALYOSS('LTAItbt8uWoW488i','ls7zl44OUoyDs5Xp09gFSdnn5MN58G','niaoting-bucket.oss-cn-shanghai.aliyuncs.com');
        //设置是否打开curl调试模式
        $this->oss_sdk_service->set_debug_mode(true);
    }

    /**
     * 
     * @param type $data
     * @param $folder 
     * @param $filename 文件名
     */
    function save($data , $folder = 'headimg' , $filename = '') {
        
        $this->folder = $folder;
        
        if(!$filename)
            $filename = time().mt_rand(1000, 9999) . '.jpg';
        
        if($data) {
            $data = base64_decode($data);
            //对图像流进行压缩
            ob_start();
            $res = imagecreatefromstring($data);
            imagejpeg($res , NULL , 70);
            $imgdata = ob_get_clean();
            if($res && $imgdata){
                $return = $this->upload_by_content($imgdata , $filename);
                unset($data);
                return $return;
            }else{
                return -1;
            }
        } else {
            return -1;
        }
    }

    //通过内容上传文件
    function upload_by_content($content , $filename){
        $bucket = C('OSS_BUCKET');
        
        if(!$this->folder){
            $this->folder = 'upload/' . date('Y-m-d', NOW_TIME);
        }
        
        $object = $this->folder . '/' . $filename;
        
        $upload_file_options = array(
            'content' => $content,
            'length' => strlen($content),
            \ALYOSS::OSS_HEADERS => array(
                'Expires' => date('Y-m-d H:i:s' , NOW_TIME),
            ),
        );
        
        $response = $this->oss_sdk_service->upload_file_by_content($bucket,$object,$upload_file_options);
//        print_r($response);exit;
        //\Think\Log::record(print_r($response,TRUE));
        if($response->status == 200) {
            //成功
            $return['status'] = 200;
            //替换掉
            $return['url'] = $response->header['_info']['url'];
        } else {
            //失败
            $return['status'] = 100;
        }
        return $return;
    }
    
    //通过multipart上传文件
    function upload_by_multi_part($data , $folder = 'upload' , $filename = '',$size){
        $bucket = C('OSS_BUCKET');
        if(!$filename)
            $filename = time().mt_rand(1000, 9999) . '.jpg';
        $object = $folder . '/' . $filename;
        $filepath = $data;  //英文

        $options = array(
                \ALYOSS::OSS_FILE_UPLOAD => $filepath,
                'partSize' => $size,
        );

        $response = $this->oss_sdk_service->create_mpu_object($bucket, $object,$options);
        if($response->status == 200) {
            //成功
            $return['status'] = 200;
            //替换掉
            $return['url']    = $response->header['_info']['url'];
        } else {
            //失败
            $return['status'] = 100;
        }
        return $return;
    }
}
