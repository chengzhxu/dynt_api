<?php

namespace Util\Controller;

/**
 * Description of ImageController
 *
 * @author Kevin
 */
class ImageController {
    function Resizerimg(){
        $method = $_SERVER['REQUEST_METHOD'];
        $url = 'http://'.str_replace('_','/',$_GET['url']);
//        imageResizer($url, $_GET['w'], $_GET['h']);
        ImageCondens($url,$_GET['w'], $_GET['h']);
    }
}
