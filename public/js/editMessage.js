var message = '';
var senderName = '';
var address = '';
var zipcode = '';
var recipient = '';
var mobile = '';
var orderId = '';
var userImage = new Image();
var canvas_w = 228;
var canvas_h = 342;
var pic_orig_w = 0;
var pic_orig_h = 0;
var userPicUrl = '';
var voiceMediaId = '';

function init() {
    $.mobile.changePage($("#messagePage"), {
        transition: "none"
    });
}

$(function() {

    $("#messagePage").on("pageinit", function() {

        output("messagePage init");
        orderId = $('#orderId').val();
        userPicUrl = $('#picUrl').val();
        voiceMediaId = $('#voiceMediaId').val();

        if (!voiceMediaId || voiceMediaId == '0') {
            $("#playVoiceMessageButton").parent("div").css("display","none");
        } else {
            $("#voiceMessageButton").val('重新录制语音留言');
        }

        userImage.onload = function() {
            pic_orig_w = userImage.width;
            pic_orig_h = userImage.height;
        }

        userImage.src = userPicUrl;

        $("#voiceMessageButton").fastClick(function() {

            output('voiceMessageButton clicked');

            uploadOrder();

            var url = "http://" + window.location.host + "/postcard/requestvoice/" + orderId;
            $.get(
                url,
                function success(data) {

                    if (data.errcode != '0' && data.code != '0') {
                        alert("Send voice request failed! code =" + data.errcode + " msg=" + data.errmsg);
                    } else {
                        if (typeof WeixinJSBridge == "undefined") {
                            alert("请在微信浏览器中运行");
                        } else {
                            WeixinJSBridge.call('closeWindow');
                        }
                    }
                }
            );
        });

        $("#playVoiceMessageButton").fastClick(function() {
            var url = 'http://' + window.location.host + '/postcard/voice?mediaId=' + voiceMediaId;
            // self.location = url;
            var audio = document.createElement("audio");
            if (audio != null && audio.canPlayType && audio.canPlayType("audio/mpeg")) {
                audio.src = url;
                audio.play();
            }
        });

        $("#submitMessageButton").fastClick(function() {
            submitMessage();
        });

    });

    function submitMessage() {

        getValueFromInput();

        if (message == "" || message == null) {
            $.mobile.showPageLoadingMsg("b", "请填写留言", true);
            setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
            return false;
        }

        if (senderName == "" || senderName == null) {
            $.mobile.showPageLoadingMsg("b", "请填写你的姓名", true);
            setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
            return false;
        }

        changePage("#addressPage");
    }

});

$(function() {

    $("#addressPage").on("pageinit", function() {

        output("addressPage init");

        $("#editAddressButton").fastClick(function() {
            var time = new Date().getTime();
            var redirect_uri = encodeURIComponent("http://paohai.ikamobile.com/wxpay/addr");
            var url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx4a41ea3d983b4538&redirect_uri="+redirect_uri+"&response_type=code&scope=snsapi_base&state="+time+"#wechat_redirect";
            self.location = url;
            return;
        });

        $("#submitAddressButton").fastClick(function() {
            submitAddress();
        });
    });

    function submitAddress() {

        // getValueFromInput();

        // if (address == "" || address == null) {
        //     $.mobile.showPageLoadingMsg("b", "请填写地址", true);
        //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        //     return false;
        // }

        // if (zipcode == "" || zipcode == null) {
        //     $.mobile.showPageLoadingMsg("b", "请填写邮编", true);
        //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        //     return false;
        // }

        // if (zipcode.length != 6 || isNaN(zipcode)) {
        //     $.mobile.showPageLoadingMsg("b", "邮编不正确", true);
        //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        //     return false;
        // }

        // if (recipient == "" || recipient == null) {
        //     $.mobile.showPageLoadingMsg("b", "请填写收信人姓名", true);
        //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        //     return false;
        // }

        // if (mobile.length == 0 || mobile == null) {
        //     $.mobile.showPageLoadingMsg("b", "请填写收信人手机号码", true);
        //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        //     return false;
        // }

        // if (mobile.length != 11 || isNaN(mobile)) {
        //     $.mobile.showPageLoadingMsg("b", "手机号码不正确", true);
        //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        //     return false;
        // }

        output("submit address, goto previewPage");
        changePage("#previewPage");
    }
});

$(function() {

    $("#previewPage").on("pageinit", function() {

        output("previewPage init");

        $("#previewConfirmButton").fastClick(function() {
            var url = "http://" + window.location.host + "/postcard/pay/" + orderId;
            output(url);
            self.location = url;
        });

        $('#previewUserImg').shadow();
    });

    $("#previewPage").on("pageshow", function() {

        output("previewPage show");
        uploadOrder();
        initPreview();
    });

    function initPreview() {

        var a, b;
        a = pic_orig_w;
        b = pic_orig_h;

        var selectedTemplateIndex = $("#templateIndex").val();
        var imageOffsetX = $("#offsetX").val();
        var imageOffsetY = $("#offsetY").val();

        if (selectedTemplateIndex > 3) {
            temp = a; a = b; b = temp;
        }
        var wRatio = canvas_w / a;
        var hRatio = canvas_h / b;
        var ratio = wRatio > hRatio ? wRatio : hRatio;
        pic_w = a * ratio;
        pic_h = b * ratio;

        var canvas = document.getElementById('previewUserImg');
        canvas.width = pic_w;
        canvas.height = pic_h;
        var ctx = canvas.getContext('2d');
        if (selectedTemplateIndex <= 3) {
            ctx.save();
            ctx.drawImage(userImage, 0, 0, userImage.width, userImage.height, 0, 0, pic_w, pic_h);
            ctx.restore();
        } else {
            ctx.save();
            ctx.translate(pic_w,0);
            ctx.rotate(90 * Math.PI / 180);
            ctx.drawImage(userImage, 0, 0, userImage.width, userImage.height, 0, 0, pic_h, pic_w);
            ctx.restore();
        }

        $("#previewTemplateImg").attr("src", "/images/small/template"+selectedTemplateIndex+".png");
        $('#previewUserImg').css({
            left: imageOffsetX * pic_w,
            top: imageOffsetY * pic_h
        });

        $("#messagePreview").text(message);
        $("#addressPreview").text(address);
        $("#zipcodePreview").text(zipcode);
        $("#senderNamePreview").text('－' + senderName);
        $("#recipientPreview").text(recipient + ' ' + mobile);
    }
});

function uploadOrder() {

    getValueFromInput();

    var url = "http://" + window.location.host + "/postcard/updateorder/" + orderId;
    var params = {
        zipcode: zipcode,
        address: address,
        recipient: recipient,
        mobile: mobile,
        message: message,
        sender: senderName,
    };
    // var params = {};
    output('url: ' + url);
    // $.post(
    //     url,
    //     params,
    //     function success(data) {
    //         if (data.code != '0') {
    //             alert("Update order failed! code =" + data.code);
    //         }
    //     },
    //     "json"
    // );

    $.ajax({
        url: url,
        type: 'POST',
        data:params,
        dataType: 'json',
        timeout: 1000,
        error: function(){
            alert('update order failed!');
        },
        success: function(result){
            // alert('success! code:' + result.code + ' msg:' + result.msg);
        }
    });
}

function getValueFromInput()
{
    message    = $("#messageinput").val();    
    senderName = $("#nameinput").val();
    // address    = $("#addressinput").val();
    // zipcode    = $("#zipcodeinput").val();
    // recipient  = $("#recipientinput").val();
    // mobile     = $("#mobileinput").val();
}
