/**
 * Created by ASUS on 2015/12/19.
 */

var cartList =  $(".cartList").html();
    cartList = '<div class="cartTit"><p></p></div>';

$(function () {
    writeDetail();
});

function writeDetail(){
    for(var i=0;i<cartArray.pName.length;i++){
        cartList +=
            '<li id="cList'+i+'">'+
            '<div class="cartDetail"><div class="cartText"><div class="selectedPro"></div><div class="cDLeft">'+
            '<div class="cartProTit"><h3><b>'+cartArray.pName[i]+'</b></h3></div>'+
            '<div class="selectedProPrice"><span class="presentSign"></span>'+
            '<span class="presentPrice">'+cartArray.pPriceNow[i]+'</span>'+
            '<span class="usedSign"></span>'+
            '<span class="usedPrice">'+cartArray.pPriceOriginal[i]+'</span>'+
            '<div class="lab-send"></div></div><div class="partBtn"><div class="btn-plu-dec"><div class="btn-plu"></div>'+
            '<p>'+cartArray.pCount[i]+'</p>'+
            '<div class="btn-dec"></div></div><div class="btn-delete"></div></div></div></div></div></li>'

        $(".cartList").html(cartList);
    }
}



