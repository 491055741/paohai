var userName = '';
var leaveMessage = '';
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

        if (voiceMediaId == '0') {
            $("#playVoiceMessageButton").parent("div").css("display","none");
        } else {
            $("#voiceMessageButton").val('重新录制语音留言');
        }

        userImage.onload = function(){
            pic_orig_w = userImage.width;
            pic_orig_h = userImage.height;
        }

        userImage.src = userPicUrl;

        $("#voiceMessageButton").fastClick(function() {

            output('voiceMessageButton clicked');

            uploadOrder();

            var url = "http://" + window.location.hostname + "/postcard/requestvoice/" + orderId;
            $.get(
                url,
                function success(data) {

                    if (data.errcode != '0') {
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
            var url = 'http://' + window.location.hostname + '/postcard/voice?mediaId=' + voiceMediaId;
            self.location = url;
        });

        $("#submitMessageButton").fastClick(function() {
            submitMessage();
        });

    });

    function submitMessage() {
        leaveMessage = $("#messageinput").val();
        if (leaveMessage == "" || leaveMessage == null) {
            $.mobile.showPageLoadingMsg("b", "请填写留言", true);
            setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
            return false;
        }

        senderName = $("#nameinput").val();
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

        $("#submitAddressButton").fastClick(function() {
            submitAddress();
        });

        $("#cityinput").val("上海");
        $("#streetinput").val("杨浦区淞沪路303号创智天地三期8号楼8楼");
        $("#zipcodeinput").val("200082");
        $("#recipientinput").val("泡泡海");
        $("#mobileinput").val("4000621186");
    });

    function submitAddress() {
        var city = $("#cityinput").val();
        if (city == "" || city == null) {
            $.mobile.showPageLoadingMsg("b", "请填写城市", true);
            setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
            return false;
        }

        var street = $("#streetinput").val();
        if (street == "" || street == null) {
            $.mobile.showPageLoadingMsg("b", "请填写街道", true);
            setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
            return false;
        }

        address = city + street;

        zipcode = $("#zipcodeinput").val();
        if (zipcode == "" || zipcode == null) {
            $.mobile.showPageLoadingMsg("b", "请填写邮编", true);
            setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
            return false;
        }

        recipient = $("#recipientinput").val();
        if (recipient == "" || recipient == null) {
            $.mobile.showPageLoadingMsg("b", "请填写收信人姓名", true);
            setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
            return false;
        }

        mobile = $("#mobileinput").val();
        if (recipient == "" || recipient == null) {
            $.mobile.showPageLoadingMsg("b", "请填写收信人手机号码", true);
            setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
            return false;
        }

        output("submit address, goto previewPage");
        changePage("#previewPage");
    }

});

$(function() {

    $("#previewPage").on("pageinit", function() {

        output("previewPage init");

        $("#previewConfirmButton").fastClick(function() {
            var url = "http://" + window.location.hostname + "/postcard/pay/" + orderId;
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
        var imageOffsetX = $("#offsetX").val();;
        var imageOffsetY = $("#offsetY").val();;

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

        $("#messagePreview").text(leaveMessage);
        $("#addressPreview").text(address);
        $("#zipcodePreview").text(zipcode);
        $("#senderNamePreview").text('－' + senderName);
        $("#recipientPreview").text(recipient + ' ' + mobile);
    }
});

function uploadOrder() {
    var url = "http://" + window.location.hostname + "/postcard/updateorder/" + orderId;
    var params = {
        zipcode: zipcode,
        address: address,
        recipient: recipient,
        mobile: mobile,
        message: leaveMessage,
        sender: senderName,
    };

    $.post(
        url,
        params,
        function success(data) {
            if (data.code != '0') {
                alert("Update order failed! code =" + data.code);
            }
        },
        "json"
    );
}
