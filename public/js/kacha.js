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
var canvas_w = 228;
var canvas_h = 342;
var pic_orig_w = 0;
var pic_orig_h = 0;
var userPicUrl = '';


$(document).ready(function() {
    // var url = window.location.href;
    // var idx = url.indexOf("#"); // if current location is not home page, go to home page when user refresh the page.
    // if (idx != -1) {
    //     window.location = url.substring(0, idx);
    //     return;
    // }
    init();
});

$.ajaxSetup({
    cache: false
});

function init() {
    applicationCacheHandeler();

    $("a.goBack").fastClick(function(e) {
        $.mobile.back();
        e.stopPropagation();
        return false;
    });

    if (typeof(document.referrer) == "undefined") {
        output("document.referrer:" + document.referrer);
        sessionStorage.referrer = document.referrer;
    } else {
        output("browser not support document.referrer.");
    }

    setTimeout(function() {
        $.mobile.changePage($("#uploadPhotoPage"), {
            transition: "none"
        });
    }, 100);
}

$(document).bind("mobileinit", function() {

    $.mobile.loadingMessage = '页面载入中';
    $.mobile.pageLoadErrorMessage = '页面载入失败';
    $.mobile.transitionFallbacks.slideout = "none";
    // jquery mobile used $.ajax() to load page for using page transition,
    // in jquery, $.ajax() method set cache option default by true, but in Android platform this will cause some problems, if loaded from cache
    // ajax request event will not be fired.
    // in order to improve the speed of loading resources, HTML5 feature application cache must to be used.
    // to fix this issue , set cache option to false before jquery mobile setting up.
    //  var agent = navigator.userAgent.toLowerCase();
    //  if (agent.match(/android/i) == "android") {
    $.ajaxSetup({
        cache: false,
        headers: {
            "Cache-Control": "no-cache"
        }
    });
    //  }

});

function applicationCacheHandeler() {
    applicationCache.onchecking = function() {
        output(" application cache checking");
    };

    applicationCache.ondownloading = function() {
        overlay();
        output(" application cache downloading");
    };

    applicationCache.onnoupdate = function() {
        output(" application cache no update");
    };

    applicationCache.onprogress = function() {
        output(" application cache progress");
    };

    applicationCache.oncached = function() {
        closeAllDialog();
        output(" application cache cached");
        //        location.reload(true); // reload the whole web page
    };

    applicationCache.onupdateready = function() {
        output(" application cache update ready");
        closeAllDialog();
        location.reload(true); // reload the whole web page
    };

    applicationCache.onerror = function() {
        closeAllDialog();
        output(" application cache error");
    };
}

function isAndroid() {
    var agent = navigator.userAgent.toLowerCase(); // on Android, when click on 'input', it doesn't scroll up automatic.
    if (agent.match(/android/i) != "android") {
        return false;
    }
    return true;
}

function clearStorage() {
    for (var i = 0, len = sessionStorage.length; i < len; i++) {
        var key = sessionStorage.key(i);
        var value = sessionStorage.getItem(key);
        output("removing " + key + " : " + value);
        sessionStorage.removeItem(key); /// ?????
    }
}

function output(text) {
    console.log(text);
}

function overlay() 
{
    if (art.dialog.list["overlay"]) {
        return art.dialog.list["overlay"];
    }
    var originalContentCSS = {};
    var originalMainCSS = {};
    art.dialog({
        id: "overlay",
        padding: 0,
        content: '<img src="./css/images/ajax-loader.gif" />',
        lock: true,
        drag: false,
        resize: false,
        fixed: true,
        init: function() {
            $(".aui_border tbody tr:nth-child(1)").hide();
            $(".aui_border tbody tr:nth-child(3)").hide();
            originalContentCSS.margin = $(".aui_content").css("margin");
            originalContentCSS.padding = $(".aui_content").css("padding");
            $(".aui_content").css({
                "margin": "0",
                "padding": "0"
            });
            originalMainCSS.paddingTop = $(".aui_main").css("padding-top");
            $(".aui_main").css({
                "padding-top": "0"
            });
        },
        close: function () {
            $(".aui_content").css(originalContentCSS);
            $(".aui_main").css(originalMainCSS);
        }
    });

    return art.dialog.list["overlay"];
}

function closeAllDialog()
{
    var list = art.dialog.list;
    for (var i in list) {
        list[i].close();
    }
}

function changePage(pageName) {
    if (isAndroid()) {
        $.mobile.changePage($(pageName), {
            transition: "none"
        });
    } else {
        $.mobile.changePage($(pageName), {
            transition: "slidefade"
        });
    }
}

$(function() {

    var pic_w = 0;
    var pic_h = 0;

    $("#uploadPhotoPage").on("pageinit", function() {

        output("uploadPhotoPage init");

        $("#submitPhotoButton").fastClick(function() {
            submitPhoto();
        });

        for (var i = 1; i <= 6; i++) {
            var name = "#templateThumbnail" + i;
            $(name).fastClick(function() {
                clickOnThumbnail(this);
            });

            if (i == 1) {
                $(name).shadow('thumbnailselected');
            } else {
                $(name).shadow('thumbnail');
            }
        }

        userName   = $("#userName").val();
        userPicUrl = $("#userPicUrl").val();

        $("#touchLayer").shadow();

        userImage.onload = function(){
            pic_orig_w = userImage.width;
            pic_orig_h = userImage.height;

            changeTemplate(1);
            bindMove();
        }

        userImage.src = userPicUrl;
    });

    function clickOnThumbnail(obj) {
        var index = $(obj).data("index");
        changeTemplate(index);
    }

    function changeTemplate(index) {
        if (selectedTemplateIndex == index)
            return;

        $("#templateThumbnail" + selectedTemplateIndex).removeClass('jquery-shadow-thumbnailselected');

        var temp;
        selectedTemplateIndex = index;

        var a, b;
        a = pic_orig_w;
        b = pic_orig_h;
        var shouldRotate = index > 3;//(a < b && index <= 3) || (a > b && index > 3);
        if (shouldRotate) {
            temp = a; a = b; b = temp;
        }
        var wRatio = canvas_w / a;
        var hRatio = canvas_h / b;
        var ratio = wRatio > hRatio ? wRatio : hRatio;
        pic_w = a * ratio;
        pic_h = b * ratio;

        var canvas = document.getElementById('userImg');
        canvas.width = pic_w;
        canvas.height = pic_h;
        var ctx = canvas.getContext('2d');
        ctx.save();
        if (shouldRotate) {
            ctx.translate(pic_w, 0);
            ctx.rotate(90 * Math.PI / 180);
            ctx.drawImage(userImage, 0, 0, userImage.width, userImage.height, 0, 0, pic_h, pic_w);
        } else {
            ctx.drawImage(userImage, 0, 0, userImage.width, userImage.height, 0, 0, pic_w, pic_h);
        }
        ctx.restore();

        $("#templateImg").attr("src", "/images/small/template" + index + ".png");

        for (var i = 1; i <= 6; i++) {
            if (i == index) {
                $("#templateThumbnail" + index).shadow('thumbnailselected');
            } else {
                $("#templateThumbnail" + i).shadow('thumbnail');
            }
        }

        if (Math.abs(pic_w - canvas_w) <= 1) { // fit width
            var offset = (pic_h - canvas_h) / 2;
            $('#userImg').css({
                'top': -offset,
                'left': 0
            });

            imageOffsetX = 0;
            imageOffsetY = - offset / pic_h;

        } else {
            var offset = (pic_w - canvas_w) / 2; // fit height
            $('#userImg').css({
                'top': 0,
                'left': -offset
            });

            imageOffsetX = - offset / pic_w;
            imageOffsetY = 0;
        }
    }

    function bindMove() {
        output("bindMove");

        var canvas = document.getElementById('userImg');
        pic_w = canvas.width;
        pic_h = canvas.height;

        jQuery('#touchLayer')
            .bind('movestart', function(e) {
            this.ox = jQuery('#userImg').position().left;
            this.oy = jQuery('#userImg').position().top;
        })
            .bind('move', function(e) {
            this.o_x = jQuery('#userImg').position().left;
            this.o_y = jQuery('#userImg').position().top;

            if (Math.abs(pic_w - canvas_w) <= 1 && Math.abs(pic_h - canvas_h) > 1) {
                // output("path A");  // move in Y
                jQuery('#userImg').css({
                    left: 0,
                    top: this.oy + e.distY
                });
            } else if (Math.abs(pic_h - canvas_h) <= 1 && Math.abs(pic_w - canvas_w) > 1) {
                // output("path B");  // move in X
                jQuery('#userImg').css({
                    left: this.ox + e.distX,
                    top: 0
                });
            } else {
                jQuery('#userImg').css({
                    left: this.ox + e.distX,
                    top: this.oy + e.distY
                });
            }
        })
            .bind('moveend', function(e) {
            // output("moveend");

            this.o_y > 0 ? jQuery('#userImg').css({
                top: 0
            }) : false;
            this.o_x > 0 ? jQuery('#userImg').css({
                left: 0
            }) : false;
            Math.round(this.o_x + pic_w) < Math.round(canvas_w) ? jQuery('#userImg').css({
                left: '',
                right: 0
            }) : false;
            Math.round(this.o_y + pic_h) < Math.round(canvas_h) ? jQuery('#userImg').css({
                top: '',
                bottom: 0
            }) : false;

            imageOffsetX = jQuery('#userImg').position().left / pic_w;
            imageOffsetY = jQuery('#userImg').position().top / pic_h;
            // output("offsetX:"+offsetX+"  offsetY:"+offsetY);
        });
    }

    function submitPhoto() {
        output("uploadPhoto");
        changePage("#messagePage");
    }
});

$(function() {

    $("#messagePage").on("pageinit", function() {

        output("messagePage init");

        $("#voiceMessageButton").fastClick(function() {

            var url = "http://" + window.location.hostname + "/postcard/voice?username=" + userName;

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
            previewConfirm();
        });

        $('#previewUserImg').shadow();
    });

    $("#previewPage").on("pageshow", function() {

        output("previewPage show");

        initPreview();
    });

    function initPreview() {

        var a, b;
        a = pic_orig_w;
        b = pic_orig_h;

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

    function previewConfirm() {
        changePage("#paymentPage");
    }

});

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

        var url = "http://" + window.location.hostname + "/postcard/placeOrder";
        var bank = isXingYeBank? 'XingYe':'other';
        var params = {
            templateIndex: selectedTemplateIndex,
            offsetX: imageOffsetX,
            offsetY: imageOffsetY,
            userName: userName,
            zipcode: zipcode,
            address: address,
            recipient: recipient,
            mobile: mobile,
            message: leaveMessage,
            sender: senderName,
            userPicUrl: userPicUrl,
            bank: bank
        };

        $.mobile.showPageLoadingMsg("b", "正在生成订单，请稍候", true);

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
            title: "我用咔嚓自己做的明信片",
            url: postcardurl,
            summary: "",
            pic: postcardurl
        });

    });

});

