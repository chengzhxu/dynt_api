var func = {
	"indexInit" : function(){
		$.post(baseurl + "welfare/index/index",{page:1},function(result){
            var str = "";
			$(".spinner").hide();
            if(result){
                var data = result['data'];
                for(var i = 0; i < data.length; i++){
                    str += "<li> <div class='productPage'><div class='proPic' style='background-image:url("+data[i].img+")' onclick='showDetail("+data[i].id+")'> <div class='countTime'> <p>&nbsp<span  id='countTime'></span></p></div><div class='proSurplus'><P>仅剩<span id='countSurplus0'>"+ data[i].buy_count +"</span>件</P></div><div id='grad'></div></div><div class='proTitle'><h3>"+ data[i].title +"</h3></div><div class='proPrice'><span class='presentSign'>¥</span><span class='presentPrice'>"+ data[i].price +"</span><span class='usedSign'>¥</span><span class='usedPrice'>"+ data[i].market_price +"</span></div></div><div class='whiteSpace'><input type='hidden' id='id' value='"+data[i].id+"'/></div></li>";
                }
                $("#content").empty().append(str);
            }

        });
	},
	"getDetail" : function(){
		$.post(baseurl + "welfare/index/detail",{id:id},function(result){
			$(".spinner").hide();
            if(result){
                var data = result['data'];
				if( parseInt(data['buy_count'])<=0 ){
					alert('此商品已售罄');
					window.location.href = baseurl;
				}
                $("#proPic").css({"background-image":"url("+data['img']+")"});
                $('#countTime0').html("");
                $('#countSurplus0').html(data['buy_count']);
                $("#proTitle").html("<h3>" + data['title'] + "</h3>");
                $('#presentPrice').html(data['price']);
                $('#usedPrice').html(data['marketprice']);
                $('#illustration').html("<h6>" + data['content'] + "</h6>");
				$('#addCart').attr('onclick',"func.addCart("+data['buy_count']+",0)");
				$('#gopay').attr('onclick',"func.addCart("+data['buy_count']+",1)");
            }
        });
	},
	"addCart"   : function(buycount,isgopay){
		var id = $("#id").val();
		if(id){
			if($(".cartNum").html()){
				cartNum = parseInt($(".cartNum").html());
			}else{
				cartNum = 0;
			}
//			if(sessionCartList[cartid]>=buycount){
//				alert('产品数量不足');return false;
//			}else if(buycount == 0){
//				alert('产品数量不足');return false;
//			}
			
			$.post(baseurl + "Cart/Index/addCart",{obj_id:id, obj_type:0, goods_count:1},function(json){
				if(json.code == 437){
					show_login("?id=" + id);
				}else if(json.code == 1110){
					alert("商品数量不足！");
					return false;
				}else if(json.code == 200){
					
					sessionCartList[cartid] = parseInt(sessionCartList[cartid])+1;
					cartNum = parseInt(cartNum)+1;
					//alert("加入购物车成功！");
					if(isgopay){
						var url = baseurl + "Cart/Display/cart";
						 window.location.href = url;
					}
					if(cartNum == 0){
						$(".cart").css("background-position","0 0");
					}else if(cartNum>0 && cartNum<10){
						$(".cart").css("background-position","0 -100px");
						$(".cartNum").html(cartNum);
					}else if(cartNum>=10){
						$(".cart").css("background-position","0 -100px");
						$(".cartNum").html(cartNum).css("left","2.6em");
					}else{
						$(".cartNum").html("");
						$(".cart").css("background-position","0 0");
					}
					
					
				}else {
					alert("加入购物车失败！");
				}
			});
		}else {
			alert("加入购物车失败！");
		}
	},
	"initCart" : function(){
		if(cartNum == 0){
			$(".cart").css("background-position","0 0");
		}else if(cartNum>0 && cartNum<10){
			$(".cart").css("background-position","0 -100px");
			$(".cartNum").html(cartNum);
		}else if(cartNum>=10){
			$(".cart").css("background-position","0 -100px");
			$(".cartNum").html(cartNum).css("left","2.6em");
		}else{
			$(".cartNum").html("");
			$(".cart").css("background-position","0 0");
		}
	},
	"getCartList" : function(){
		$.post(baseurl + "cart/index/getCartList",{},function(result){
			$(".spinner").hide();
            if(result){
                var str = '<div class="cartTit"><p>&nbsp;&nbsp;商品信息</p></div>';
                if(result.code == 437){
                    alert("未登录!");
                    $(".tolCost").html(0);
                    $(".saveCost").html(0);
                }else{
                    var data = result['data'];
                    var cartList = data.cartList;
                    
                    var nprice = 0;
                    var oprice = 0;
                    if(cartList.length > 0){
                        for(var i = 0; i < cartList.length; i++){
                            nprice += cartList[i].price * cartList[i].goods_count;
                            oprice += cartList[i].marketprice * cartList[i].goods_count;
                            str += '<li id="cList'+i+'">'+
                                '<div class="cartDetail"><div class="cartText"><div class="selectedPro" style="background-image:url('+cartList[i].img+')"></div><div class="cDLeft">'+
                                '<div class="cartProTit"><h3><b>'+cartList[i].title+'</b></h3></div>';
//                            str +=  '<div class="selectedProPrice"><span class="presentSign"></span>'+
//                                '<span class="presentPrice" id="price'+cartList[i].id+'">'+cartList[i].price+'</span>'+
//                                '<span class="usedSign"></span>'+
//                                '<span class="usedPrice" id="mprice'+cartList[i].id+'">'+cartList[i].marketprice+'</span>'+
//                                '<div class=""></div></div>;
							str += '<div class="partBtn"><div class="btn-plu-dec"><div class="btn-plu" onclick="func.btnPlu('+cartList[i].id+')"></div>'+
                                '<p id="'+cartList[i].id+'">'+cartList[i].goods_count+'</p>'+
                                '<div class="btn-dec" onclick="func.btnDec('+cartList[i].id+')"></div></div><div class="btn-delete" onclick="func.btnDelete('+i+','+cartList[i].id+')"></div></div></div></div></div>'+
                                '<input type="hidden" id="buy_count'+cartList[i].id+'" value="'+cartList[i].buy_count+'"/></li>';
                        }
                        var dprice = oprice - nprice;
//                        $(".tolCost").html(fomatFloat(nprice,2));  //TODO
//                        $(".saveCost").html(fomatFloat(dprice,2)); //TODO
                        
                    }else{
//                        $(".tolCost").html(0);   //TODO
//                        $(".saveCost").html(0);  //TODO
                        //str += "您还没有添加商品到购物车！";
						empty==0?func.hasPro():func.emptyCart();
                    }
                }
                $(".cartList").empty().append(str);
            }
        });
	},
	"emptyCart"  : function(){
		$(".emptyCart").removeClass("hide");
		$(".layout").addClass("hide");
	},
	"hasPro"     : function() {
		$(".layout").removeClass("hide");
		$(".emptyCart").addClass("hide");
	},
	"btnPlu"     : function(id){
		var count = $('#'+id).html();
		if(parseFloat(count) > 1){
			if(d){
				d = false;
				$.post(baseurl + "Cart/Index/changegoods",{id:id, amount:-1, obj_type:0},function(result){
					d = true;
					if(result.code == 200){
						$('#'+id).html(parseFloat(count) - 1);
						var price  = $("#price" + id).html();
						var mprice = $("#mprice" + id).html();
						var nprice = $(".tolCost").html();
						var dprice = $(".saveCost").html();
						$(".tolCost").html(fomatFloat(fomatFloat(nprice, 2) - fomatFloat(price, 2),2));
						$(".saveCost").html(fomatFloat(fomatFloat(dprice, 2) - fomatFloat(mprice, 2) + fomatFloat(price, 2),2));
					}
				});
			}
		}
	},
	"btnDec"   : function(id){
		var buy_count = $("#buy_count"+id).val();
		var count = $('#'+ id).html();
		if(parseFloat(count) < parseFloat(buy_count)){
			if(d){
				d = false;
				$.post(baseurl + "Cart/Index/changegoods",{id:id, amount:1, obj_type:0},function(result){
					d = true;
					if(result.code == 200){
						$("#"+id).html(parseFloat(count) + 1);
						var price  = $("#price" + id).html();
						var mprice = $("#mprice" + id).html();
						var nprice = $(".tolCost").html();
						var dprice = $(".saveCost").html();
						$(".tolCost").html(fomatFloat(fomatFloat(nprice, 2) + fomatFloat(price, 2), 2));
						$(".saveCost").html(fomatFloat(fomatFloat(dprice, 2) + fomatFloat(mprice, 2) - fomatFloat(price, 2), 2));
					}
				});
			}
		}else{
			alert("商品数量不足，无法添加");
		}
	},
	"btnDelete"  : function(i , id){
		if(window.confirm('你确定要删除该商品吗？')){
			$.post(baseurl + "Cart/Index/delCart",{id:id},function(result){
				if(result.code == 200){
					var cList  = $("#cList"+i);
					var price  = $("#price" + id).html();
					var count  = $("#"+id).html();
					var mprice = $("#mprice" + id).html();
					var nprice = $(".tolCost").html();
					var dprice = $(".saveCost").html();
					$(".tolCost").html(fomatFloat(nprice - price * count, 2));
					var p = fomatFloat(mprice, 2) * count - fomatFloat(price, 2) * count;
					$(".saveCost").html(fomatFloat(dprice - p, 2));
					$(".cartList").find(cList).remove();
					if($(".cartList li").length == 0){
						$(".emptyCart").removeClass("hide");
						$(".layout").addClass("hide");
					}
				}
			});
		}
	},
	"getPackets" : function() {
		$.post(baseurl + "Cart/Index/getPackets",{},function(result){
			if(result.code == 200){
				var html = '';
				
				for(var i = 0 ; i < result.data.length;i++) {
					
					html += '<li ';
					html += ' class="ticketList ';
					if(result.data[i]['status'] == 0)
						html += ' overDue ';
					
					if(result.data[i]['id'] == packet_id)
						html += ' active';
					html += '"';
					
					if(result.data[i]['status'] == 1)
						html += ' onclick="selectPacket('+ result.data[i]['id'] +');"';
					
					html += '><div class="liTop"></div>';

					html += '<div class="liMid"><div class="tDetail"><ul class="line'+(result.data[i]['falsenum']+1)+'">';
					
					for(var j = 0 ; j < result.data[i].text.length;j++) {
						html += '<li>'+result.data[i].text[j]+'</li>';
					}
					html += '<li>'+result.data[i]['start_time']+'~'+result.data[i]['over_time']+'</li></ul></div></div><div class="liBottom"></div>';

					html += '<div class="tValue"><p class="vNum';
					
					if(result.data[i]['status'] == 0)
						html += ' overDue ';
					
					html += '">￥<span>'+ result.data[i]['money']+'元</span></p></div>';
					if(result.data[i]['id'] == result.firstData.id)
						html += '<p class="cheapest"></p>';
					
					html += '</div><P class="cPatternT"></P><P class="cPatternD"></P></li>';
				}
				$('.ticketUl').html(html);
			}
		});
	}
};

var pay = {
	"getAddress"  : function(backid) {
		if(backid){
			$.post(baseurl + "Member/Index/getoneaddress",{id:backid},function(result){
				if(result){
					$('.addShow').removeClass('noaddress');
						//收货地址显示
						var data = Array();
						data[0] = result['data'];
						for(var i = 0; i < data.length; i++){
							tel.push(data[i].mobile);
							add.push(data[i].full_address);
							username.push(data[i].username);
							addressid.push(data[i].id);	   
						}
						
						selectedAdd = {
							"username":username,
							"tel":tel,
							"add":add,
							"addressid":addressid
						};
						
						pay.writeAdd();
				}else{
					alert(result['code']);
					$('.addShow').addClass('noaddress');
					$('.addShow').html('添加收货地址');
					$('.addShow').click(function(){
						var url = baseurl + "member/display/addaddress?go=pay";
						location.href=url;
					});
				}
			})
		}else{
			$.post(baseurl + "Member/Index/getaddress",'',function(result){
				if(result['code'] == '437'){
					alert('请登录');
				}else{
					if(result['data'][0]){
						$('.addShow').removeClass('noaddress');
						//收货地址显示
						var data = result['data'];
						for(var i = 0; i < data.length; i++){
							tel.push(data[i].mobile);
							add.push(data[i].full_address);
							username.push(data[i].username);
							addressid.push(data[i].id);	   
						}
						
						selectedAdd = {
							"username":username,
							"tel":tel,
							"add":add,
							"addressid":addressid
						};
						
						pay.writeAdd();
						
					}else{
						$('.addShow').addClass('noaddress');
						$('.addShow').html('添加收货地址');
						$('.addShow').click(function(){
							var url = baseurl + "member/display/addaddress?go=pay";
							location.href=url;
						});
					}
					
					
				}
			});
		}
	},
	"writeAdd"  : function() {
		addList ='<p class="spaceTop" value="'+selectedAdd.addressid[0]+'">'+
            '<span class="addName">'+selectedAdd.username[0]+'</span>'+
            '<span class="addTel">'+selectedAdd.tel[0]+'</span></p>'+
            '<p class="addD">'+selectedAdd.add[0]+'</p>';
        $(".addRPart").html(addList);
	},
	"writeDetail"  : function(){
		for(var i=0;i<cartArray.pName.length;i++){
			sCartList +=
				'<li id="cList'+i+'">'+
				'<div class="cartDetail"><div class="cartText"><div class="selectedPro" style="background-image:url('+cartArray.img[i]+')"></div><div class="cDLeft">'+
				'<div class="cartProTit"><h3><b>'+cartArray.pName[i]+'</b></h3></div>'+
				 '<div class="selectedProPrice"><span class="presentSign">¥</span>'+
				 '<span class="presentPrice">'+cartArray.pPriceNow[i]+'</span>'+
				 '<span class="usedSign">¥</span>'+
				  '<span class="usedPrice">'+cartArray.pPriceOriginal[i]+'</span>'+
				  '<span class="pCountNum">x'+cartArray.pCount[i]+'</span></div></div></div></div></li>';

			$(".cListUl").html(sCartList);
		}
	},
	"getCartList" : function(){
		$.post(baseurl + "Cart/Index/getCartList",{packet_id:packet_id},function(result){
			$(".spinner").hide();
			if(result['code'] == '437'){
				//alert('请登录');
			}else{
				var data = result['data'];
				cartList = data['cartList'];
				if(cartList.length <=0 ){
					$(".emptyCart").removeClass("hide");
					$(".layout").addClass("hide");
				} else {
					for(var i = 0; i < cartList.length; i++){
						pName.push(cartList[i].title);
						pPriceNow.push(cartList[i].price);
						pPriceOriginal.push(cartList[i].marketprice);
						pCount.push(cartList[i].goods_count);	
						img.push(cartList[i].img);
						marketMoney += (fomatFloat(cartList[i].marketprice,2)-fomatFloat(cartList[i].price,2))*parseInt(cartList[i].goods_count);
						realMoney += (fomatFloat(cartList[i].price,2))*parseInt(cartList[i].goods_count);

					}
					payMoney = realMoney;
                    var packets = data.packets;
                    if(packets.number > 0) {
                    	
                    	var html = '<p class="leftIcon" onclick="packet(this);"></p><a href="'+baseurl + 'Cart/Display/getPacketList?packet_id='+packets.firstData.id+'&backid='+$('.spaceTop').attr('value')+'"><div class="costAndTime">';
                    	html += '<p class="cost center"><span>￥</span><span>'+ packets.firstData.money +'元</span></p>';
                        html += '<p class="time center"><span>有效期:</span><span>'+packets.firstData.gettime+'到'+packets.firstData.overtime+'</span></p>';
                        html += '</div><p class="transIcon"></p></a>';
                        
                    	$(".ticketRD").html(html);
                    	realMoney = realMoney - packets.firstData.money;
                    	if(realMoney < 0)
                    		realMoney = 0;
                    	
                    	packet_id = packets.firstData.id;
                    	packet_money = fomatFloat(packets.firstData.money,2);
                    } else {
                    	var html = '<p class="leftIcon" style="visibility: hidden"></p><div class="costAndTime"><p class="noTicket center">暂无红包</p>';
                    	html += '<p class="btn-getT center"><a href="http://w1.skinrun.me/redpacket/index/id/1" target="_blank">去领取</a></p></div>';
                        html += '<p class="transIcon" style="visibility: hidden"></p>';
                    	$(".ticketRD").html(html);
                    }
                    
					$('.tolCost').html(fomatFloat(realMoney,2));
					$('.saveCost').html(fomatFloat(marketMoney,2));
					cartArray = {
						"pName":pName,
						"pPriceNow":pPriceNow,
						"pPriceOriginal":pPriceOriginal,
						"pCount":pCount,
						"img":img
					};
					pay.writeDetail();
				}
			}
		});
	},
	"btnbuyNow"  : function(){
		$(".spinner").show();
		$(".myMask").show();
		remark = $('#remark').text();
		is_credit = 0;
		var address_id= $('.spaceTop').attr('value');
		payArray['cart'] = cartList;
		//支付
		$.post(baseurl + "Cart/Index/getverifycart",{remark:remark,channel:channel,openid:openid,is_credit:is_credit,address_id:address_id,cart:cartList,packet_id:packet_id},function(res){
			$(".spinner").hide();
			$(".myMask").hide();
			if(res['code'] == 437){
				 show_login();
			}else if(res['code'] == 200){
				pingpp.createPayment(res['data'], function(result, error){
					if (result == "success") {
						window.location.href = baseurl + 'Cart/Pay/result?out_trade_no='+ res['data']['order_no'];
						// 只有微信公众账号 wx_pub 支付成功的结果会在这里返回，其他的 wap 支付结果都是在 extra 中对应的 URL 跳转。
					} else if (result == "fail") {
						//alert(JSON.stringify(error));
						//return false;
						window.location.href = baseurl + 'Cart/Pay/result';
						// charge 不正确或者微信公众账号支付失败时会在此处返回
					} else if (result == "cancel") {
						window.location.href = baseurl + 'Cart/Pay/result';
						// 微信公众账号支付取消支付
					}
				});
			}else if(res['code'] == 1105){
				alert('购物车不存在');
			}else if(res['code'] == 1111){
				window.location.href = baseurl + 'Cart/Pay/result?out_trade_no='+ res['data']['order_no'];
			}else if(res['code'] == 1114){
				alert('红包不存在或己使用');
				return false;
			}else if(res['code'] == 1115){
				alert('红包选择错误');
				return false;
			}else{
				alert('商品数量不足');
			}
		});
	}
};

function showDetail(id){
	if(id){
		window.location.href = "/welfare/display/detail?id=" + id;
	}
}
function fomatFloat(src,pos){      
	return Math.round(src*Math.pow(10, pos))/Math.pow(10, pos);
}