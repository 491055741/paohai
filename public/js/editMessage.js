var salutation = '';
var message = '';
var signature = '';

var recipient = '';
var address = '';
var zipcode = '';
var mobile = '';

var orderId = '';
var voiceMediaId = '';


$(document).on("pageinit", "#messagePage", function() {

    output("messagePage init");

    orderId = $('#orderId').val();
    voiceMediaId = $('#voiceMediaId').val();

    if (!voiceMediaId || voiceMediaId == '0') {
        $("#playVoiceMessageButton").css("display","none");
    } else {
        $("#voiceMessageButton").attr({'src': '/images/small/rerecord_voice_btn.png'});
        $("#voiceMessageButton").css({
                width:50,
                height:50
            });
    }

    $("#voiceMessageButton").fastClick(function() {
        output('voiceMessageButton clicked');
        uploadOrder(sendVoiceRequest);
    });

    $("#playVoiceMessageButton").fastClick(function() {
        var url = 'http://' + window.location.host + '/postcard/voice?mediaId=' + voiceMediaId + "&nonce=" + getNonceStr();
        var audio = document.createElement("audio");
        if (audio != null && audio.canPlayType && audio.canPlayType("audio/mpeg")) {
            audio.src = url;
            audio.play();
        }
    });

    $("#submitMessageButton").fastClick(function() {
        submitMessage();
    });

    $("#messageInput").keyup(function() {
        var maxCount = 160;
        var content = $(this).val();
        var count = content.length; 
        if (count > maxCount) {
            $(this).val(content.substring(0, maxCount));
        }
    });
});

function sendVoiceRequest() {
    var url = "http://" + window.location.host + "/postcard/requestvoice/" + orderId + "?nonce=" + getNonceStr();
    $.get(
        url,
        function success(data) {

            if (data.errcode != '0' && data.code != '0') {
                alert("Send voice request failed! code =" + data.errcode + " msg=" + data.errmsg);
            } else {
                if (typeof WeixinJSBridge == "undefined") {
                    alert("不支持方法'closeWindow'. 请在微信浏览器中运行");
                } else {
                    WeixinJSBridge.call('closeWindow');
                }
            }
        }
    );    
}

function submitMessage() {

    getValueFromInput();

    if (salutation == "" || salutation == null) {
        $.mobile.showPageLoadingMsg("b", "请填写收信人称呼", true);
        setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        return false;
    }

    if (message == "" || message == null) {
        $.mobile.showPageLoadingMsg("b", "请填写留言", true);
        setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        return false;
    }

    if (signature == "" || signature == null) {
        $.mobile.showPageLoadingMsg("b", "请填写签名", true);
        setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        return false;
    }

    uploadOrder(gotoAddressPage);
}

function gotoAddressPage() {
    if (typeof WeixinJSBridge == 'undefined') {
        var url = "http://" + window.location.host + "/wxpay/address?state="+orderId+"&code=10010";
    } else { // can't open below url outside wechat
        var redirect_uri = encodeURIComponent("http://paohai.ikamobile.com/wxpay/address");
        var url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx4a41ea3d983b4538&redirect_uri="+redirect_uri+"&response_type=code&scope=snsapi_base&state="+orderId+"#wechat_redirect";
    }
    self.location = url;
}

function uploadOrder(callback) {

    getValueFromInput();

    var url = "http://" + window.location.host + "/postcard/updateorder/" + orderId + "?nonce=" + getNonceStr();
    var params = {
        salutation: salutation,
        message: message,
        signature: signature,
    };

    output('url: ' + url);
    $.ajax({
        url: url,
        type: 'POST',
        data:params,
        dataType: 'json',
        timeout: 10000,
        error: function(xmlhttprequest, err, e){
            if (err == 'timeout') {
                alert("网速不给力，请稍后再试哦");
            } else {
                alert('update order failed!');
            }
        },
        success: function(result) {
            callback();
        }
    });
}

function getValueFromInput()
{
    salutation = $("#salutationInput").val();
    message    = $("#messageInput").val();    
    signature  = $("#signatureInput").val();
}
