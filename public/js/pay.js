var userName = '';
var selectedTemplateIndex = 0;
var leaveMessage = '';
var senderName = '';
var address = '';
var zipcode = '';
var recipient = '';
var mobile = '';
var userImage = new Image();
var imageOffsetX = 0;
var imageOffsetY = 0;
var userPicUrl = '';

function init() {
    $.mobile.changePage($("#paymentPage"), {
        transition: "none"
    });
}

$(function() {

    $("#paymentPage").on("pageinit", function() {

        output("paymentPage init");

        $("#submitPaymentButton1").fastClick(function() {
            // self.location = "http://paohai.ikamobile.com/wxpaydemo.html";
            submitPayment(false);
        });

        $("#submitPaymentButton2").fastClick(function() {
            submitPayment(true);
        });

    });

    function submitPayment(isXingYeBank) {

        // changePage("#payingPage");

        var url = "http://" + window.location.hostname + "/postcard/placeorder";
        var bank = isXingYeBank? 'XingYe':'other';
        var params = {
            // templateIndex: selectedTemplateIndex,
            // offsetX: imageOffsetX,
            // offsetY: imageOffsetY,
            // userName: userName,
            // zipcode: zipcode,
            // address: address,
            // recipient: recipient,
            // mobile: mobile,
            // message: leaveMessage,
            // sender: senderName,
            // userPicUrl: userPicUrl,
            bank: bank
        };

        $.mobile.showPageLoadingMsg("b", "请稍候", true);

        $.post(
            url,
            params,
            function success(data) {
                // alert("post ok");
                $.mobile.hidePageLoadingMsg();
                if (data.code != '0') {
                    alert("Place order failed! code =" + data.code);
                } else {
                    var url = 'http://' + window.location.hostname + '/wxpay/pay/' + data.orderId + '?bank=' + bank;
                    // alert(url);
                    self.location = url;
                }
            },
            "json"
        );
    }
});

$(function() {
    $("#payingPage").on("pageinit", function() {

        output("payingPage init");

        $("#submitPayDoneButton").fastClick(function() {
            submitPayDone();
        });

        var width = $(document.body).width();
        var padding = parseInt($('.ui-content').css("padding"));
        $('#payImg').css({
            'margin-top': -padding + 'px',
            'margin-left': -padding + 'px',
            width: width + 2 * padding + 'px',
        });
    });

    function submitPayDone() {

        changePage("#completePage");
    }
});

$(function() {

    $("#completePage").on("pageinit", function() {

        output("completePage init");
    });

    $("#completePage").on("pageshow", function() {

        output("completePage show");

        var postcardurl = "http://" + window.location.hostname + "/postcard/preview?templateIndex="+selectedTemplateIndex+
                        "&offsetX="+imageOffsetX+
                        "&offsetY="+imageOffsetY+
                        "&userPicUrl="+userPicUrl;
        bShare.addEntry({
            title: "我的泡海明信片",
            url: postcardurl,
            summary: "",
            pic: postcardurl
        });

    });

});

