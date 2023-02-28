//js判断是否是IOS设备
function checkIsAppleDevice() {
    var u = navigator.userAgent, app = navigator.appVersion;
    var ios = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    var iPad = u.indexOf('iPad') > -1;
    var iPhone = u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1;
    if (ios || iPad || iPhone) {
        return true;
    } else {
        return false;
    }
}

//js判断是否为Android设备
function checkIsAndroidDevice(){
    var u = navigator.userAgent;
    if ( u.indexOf('Android') > -1 || u,indexOf('Adr') > -1 ){
        return true;
    }else{
        return false;
    }
}

function down_app(){
    if (checkIsAppleDevice()) {
        window.location.href = "https://itunes.apple.com/app/id1241365109?mt=8";//跳转到AppStore
    } else if(checkIsAndroidDevice()) {
        window.location.href = "";//打开apk
    }
}