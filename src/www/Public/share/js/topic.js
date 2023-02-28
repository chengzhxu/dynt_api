window.onload=function(){
    var img_height = '180';
    // 获取窗口宽度
    var winWidth = document.body.clientWidth;
    img_height = winWidth / 3;
    var thumbs = document.getElementsByName('thumb');
    var ih = img_height + 'px';
    for(var i = 0; i < thumbs.length; i++) {
        thumbs[i].style.height = ih;
    }
}

