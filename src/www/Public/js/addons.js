/*@charset "gb2312";*/

$(function(){
	 var wechat_developer_reload = function(){
		$('body').append('<input type="button" value="refresh" onclick="window.location.reload();"/>');	 
	};
	//wechat_developer_reload();
	
	/*if (window.console) {
		console.info(">>Join us? Email：developer@qietu.com");
	}*/		
	
	//页面不足一屏，铺满一屏
	$('.layout').css('min-height',$(window).height());
	
	$('.mask').click(function(){
		$('.mask,.dialog').fadeOut();
	});
	
	$('.start').click(function(){
		
		if(!$(this).hasClass('selected')){
			return false;
		}
		
		$(this).removeClass('selected');
//		初始化
		$('.game li').removeClass('selected');
		$('.game li:first').addClass('selected');
		
		//游戏
		gamerun = setInterval(function(){
			curr = $('.game li.selected');
			if(curr.next().size()>0){
				next = curr.next();
			}
			else{
				next = $('.game li:first');
			}
			curr.removeClass('selected');
			next.addClass('selected');
		},200);
		
//		随机数
		//randNum = Math.floor(Math.random()*5000+500);
		
		// 0 : 10元红包
		// 1 : 肌肤测试仪
		// 2 : 易科美
		// 3 : 再来一次
		// 4 : DML睫毛护养液
		// 5 : 5元现金红包
		// 6 : 肌肤管家100元代金券
		// 7 : 谢谢参与
		arr = [6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,1,4,4,5,0];
		rand_num = parseInt(arr.length*Math.random()); //获取数组下标的随机数
		//alert(arr[num]);
		
		num = arr[rand_num];
		if(!$('body').hasClass('shared')){
			num = 7;  //未分享概率为0
		}
		if($('body').hasClass('geted')){
			num = 7;  //以获取奖品，概率为0
		}

//		定时
		setTimeout(function(){
			clearInterval(gamerun);
			$('.start').addClass('selected');
			//alert(3);
		},1600+200*num); // 1600代表一圈200*8
	})
	
});

