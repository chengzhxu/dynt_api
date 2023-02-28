/**
 * Created by ASUS on 2015/12/19.
 */

var addList =  $(".addUl").html();
    addList = "<div></div>";

$(function () {
    writeDetail();
    writeCss();
});

function writeDetail(openid){
    for(var i=0;i<user.add.length;i++){
		if(user.isdefault[i] == 0){
			isdefaultstr = '<div class="btn-default" style="visibility: visible;"></div>';
		}else{
			isdefaultstr = '<div class="btn-default" style="visibility: hidden;"></div>';
		}
        addList +=  '<li class="addressList">'+
            '<a style="color:#000;text-decoration:none;" href="/cart/display/onlinepay?id='+user.addressid[i]+'&openid='+openid+'"><p class="spaceTop"><span class="name" id="name'+i+'"></span><span class="tel" id="tel'+i+'"></span></p>' +
            '<p class="addDetail" id="add' + i + '"></p>' +'<span class="defaultAdd"></span>'+
			'<input class = "address" type="hidden" value="" id="address' + i + '">' +
			'<input class="isdefault" type="hidden" value="" id="isdefault' + i + '"></a>' +
            '<div class="btn-m&d">'+
            '<div class="btn-delete"></div>'+
            '<div class="btn-modify"></div>'+
			isdefaultstr+
            '</div></li>';
        $(".addUl").html(addList);
    }
}

function writeCss(){
    for(var i=0;i<user.add.length;i++){
        $("#name" + i).html(user.name[i]);
        $("#tel" + i).html(user.tel[i]);
        $("#add" + i).html(user.add[i]);
		$("#address" + i).val(user.addressid[i]);
		$("#isdefault" + i).val(user.isdefault[i]);
    }
}