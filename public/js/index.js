var orderId = '';
var userName = '';
var selectedTemplateIndex = 0;
var userImage = new Image();
var imageOffsetX = 0;
var imageOffsetY = 0;
var canvas_w = 228;
var canvas_h = 342;
var pic_orig_w = 0;
var pic_orig_h = 0;
var userPicUrl = '';
var pic_w = 0;
var pic_h = 0;
var isTemplateOpen = false;

$(document).on("pageinit", "#makePicturePage", function() {

    output("makePicturePage init");
    imageOffsetX = $("#offsetX").val();
    imageOffsetY = $("#offsetY").val();

    orderId    = $("#orderId").val();
    userName   = $("#userName").val();
    userPicUrl = $("#userPicUrl").val();

    $("#submitPhotoButton").fastClick(function() {
        submitPhoto();
    });

    $("#toggleTemplateButton").fastClick(function() {
        toggleTemplateList();
    });

    $("#templateContainer").owlCarousel({pagination:false, itemsMobile:[2000, 5.5]});

    for (var i = 1; i <= 10; i++) {
        var name = "#templateThumbnail" + i;
        $(name).tap(function() {
            clickOnThumbnail(this);
        });
    }

    toggleTemplateList();
    $("#touchLayer").shadow();

    userImage.onload = function(){

        pic_orig_w = userImage.width;
        pic_orig_h = userImage.height;

        // drawBackground();

        changeTemplate($("#templateIndex").val());

        if (imageOffsetX != 0 || imageOffsetY != 0) {
            placePicture();
        }
        bindMove();
    }
    userImage.src = userPicUrl;
});

function placePicture() {
    var a, b;
    a = pic_orig_w;
    b = pic_orig_h;
    var shouldRotate = selectedTemplateIndex > 6;
    if (shouldRotate) {
        temp = a; a = b; b = temp;
    }
    var wRatio = canvas_w / a;
    var hRatio = canvas_h / b;
    var ratio = wRatio > hRatio ? wRatio : hRatio;
    pic_w = a * ratio;
    pic_h = b * ratio;

    $('#userImg').css({
        left: imageOffsetX * pic_w,
        top: imageOffsetY * pic_h
    });
}

function toggleTemplateList() {
    if (isTemplateOpen) { // hide
        $(".postcard_small").addClass("postcard")
        $(".postcard_small").removeClass("postcard_small")
        $("#templateContainer").hide("normal");

        $("#toggleTemplateButton").css(/*[huangchun 2014-9-18]top值由450改为90%，不能固定高度*/
            "top","90%"
        );
        $("#toggleTemplateButtonImg").attr("src", "/images/small/unfold_btn.png");
        $("#toggleTemplateButtonImg").css({
            width:68,
            height:20
        });

    } else { // show
        $(".postcard").addClass("postcard_small")
        $(".postcard").removeClass("postcard")
        $("#templateContainer").show("normal");
        $("#toggleTemplateButton").css(
            /*[huangchun 2014-9-18] top由380改为70%*/
            "top","67%"
        );

        $("#toggleTemplateButtonImg").attr("src", "/images/small/fold_btn.png");
        $("#toggleTemplateButtonImg").css({
            width:20,
            height:20
        });

        /*[huangchun 2014-9-17] 动态设置$(".ui-content>div")的高*/
        $(".ui-content>div").css("height","80%");
        /*huangchun 2014-9-26 给定宽高比，动态设置高度*/
        $(".postcard_small").height($(".postcard_small").width/(460/680));
    }
    isTemplateOpen = !isTemplateOpen;
}

function clickOnThumbnail(obj) {
    var index = $(obj).data("index");
    changeTemplate(index);
}

function changeTemplate(index) {
    if (selectedTemplateIndex == index)
        return;

    // $("#templateThumbnail" + selectedTemplateIndex).removeClass('jquery-shadow-thumbnailselected');
    $("#templateThumbnail" + selectedTemplateIndex).removeClass('thumbnail_selected');
    $("#templateThumbnail" + selectedTemplateIndex).addClass('thumbnail');
    $("#templateThumbnail" + index).removeClass('thumbnail');
    $("#templateThumbnail" + index).addClass('thumbnail_selected');

    var temp;
    selectedTemplateIndex = index;

    var a, b;
    a = pic_orig_w;
    b = pic_orig_h;
    var shouldRotate = index > 6;
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

    // for (var i = 1; i <= 6; i++) {
    //     if (i == index) {
    //         $("#templateThumbnail" + index).shadow('thumbnailselected');
    //     } else {
    //         $("#templateThumbnail" + i).shadow('thumbnail');
    //     }
    // }

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
            this.ox = $('#userImg').position().left;
            this.oy = $('#userImg').position().top;
        })

        .bind('move', function(e) {
            this.o_x = $('#userImg').position().left;
            this.o_y = $('#userImg').position().top;

            if (Math.abs(pic_w - canvas_w) <= 1 && Math.abs(pic_h - canvas_h) > 1) {
                // output("path A");  // move in Y
                $('#userImg').css({
                    left: 0,
                    top: this.oy + e.distY
                });
            } else if (Math.abs(pic_h - canvas_h) <= 1 && Math.abs(pic_w - canvas_w) > 1) {
                // output("path B");  // move in X
                $('#userImg').css({
                    left: this.ox + e.distX,
                    top: 0
                });
            } else {
                $('#userImg').css({
                    left: this.ox + e.distX,
                    top: this.oy + e.distY
                });
            }
        })

        .bind('moveend', function(e) {
            // output("moveend");

            this.o_y > 0 ? $('#userImg').css({
                top: 0
            }) : false;
            this.o_x > 0 ? $('#userImg').css({
                left: 0
            }) : false;
            Math.round(this.o_x + pic_w) < Math.round(canvas_w) ? $('#userImg').css({
                left: '',
                right: 0
            }) : false;
            Math.round(this.o_y + pic_h) < Math.round(canvas_h) ? $('#userImg').css({
                top: '',
                bottom: 0
            }) : false;

            imageOffsetX = $('#userImg').position().left / pic_w;
            imageOffsetY = $('#userImg').position().top / pic_h;
            // output("offsetX:"+offsetX+"  offsetY:"+offsetY);
        });
}

function submitPhoto() {

    if (orderId == '0') { // new order
        var url = "http://" + window.location.host + "/postcard/placeorder" + "?nonce=" + getNonceStr();
        var params = {
            templateIndex: selectedTemplateIndex,
            offsetX: imageOffsetX,
            offsetY: imageOffsetY,
            userName: userName,
            userPicUrl: userPicUrl
        };

        $.post(
            url,
            params,
            function success(data) {
                // alert("post ok");
                if (data.code != '0') {
                    alert("Place order failed! code =" + data.code);
                } else {
                    // alert("Place order success");
                    orderId = data.orderId;
                    var url = "http://" + window.location.host + "/postcard/editmessage/" + orderId + "?nonce=" + getNonceStr();
                    output(url);
                    self.location = url;
                }
            },
            "json"
        );
    } else { // modify order
        uploadOrder(function() {
                    var url = "http://" + window.location.host + "/postcard/editmessage/" + orderId + "?nonce=" + getNonceStr();
                    output(url);
                    self.location = url;
        });
    }
}

function uploadOrder(callback) {

    var url = "http://" + window.location.host + "/postcard/updateorder/" + orderId + "?nonce=" + getNonceStr();
    var params = {
        templateIndex: selectedTemplateIndex,
        offsetX: imageOffsetX,
        offsetY: imageOffsetY
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

