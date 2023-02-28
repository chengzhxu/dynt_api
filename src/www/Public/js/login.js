var go_path = "";

function show_login(path){
    $(".signIn").removeClass("hide").addClass("moveD");
    $(".myMask").css("display","block");
    go_path = path;
}

function hidden_login(){
    $(".signIn").addClass("hide");
    $(".myMask").css("display","none");
}


var s = true;
var wait=60;
function time(o) {
    if(s){
        var type = "register";
        var mobile = $('#mobile').val();
        $.post("/Member/Index/verifycode",{type:type,mobile:mobile},function(result){
            if(result){
                if(result.code == 404){
                    alert("手机号码不合法，请重新输入！");
                }else if(result.code == 200){
                    alert("验证码已发送，请稍等！");
                }
            }
        });
    }
    if (wait == 0) {
        o.removeAttribute("disabled");	
        o.value = "获取验证码";
        wait = 60;
        s = true;
    } else {
        s = false;
        o.setAttribute("disabled", true);
        o.value = wait + "秒后重新获取";
        wait--;
        setTimeout(function() {
            time(o);
        },
        1000);
    }
}

/**
* 登录
* */
function go_login(){
   var mobile = $('#mobile').val();
   var code = $('#verifycode').val();
   if(mobile != "" && code != ""){
       $.post("/Member/Index/register",{mobile:mobile,verify_code:code},function(result){
           if(result){
               if(result.code == 421){
                   alert("手机号码不能为空");
               }else if(result.code == 422){
                   alert("验证码不能为空");
               }else if(result.code == 424){
                   alert("验证码已过期，请重新获取");
               }else if(result.code == 200){
                   hidden_login();
                    window.location.href = go_path;
                   
               }
           }
       });
   }else{
       alert("请输入信息");
   }
}

