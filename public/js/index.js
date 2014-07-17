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


function init() {
    $.mobile.changePage($("#uploadPhotoPage"), {
        transition: "none"
    });
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
        var shouldRotate = index > 3;
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

        var url = "http://" + window.location.hostname + "/postcard/placeorder";
        var params = {
            templateIndex: selectedTemplateIndex,
            offsetX: imageOffsetX,
            offsetY: imageOffsetY,
            userName: userName,
            userPicUrl: userPicUrl,
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
                    var orderId = data.orderId;
                    var url = "http://" + window.location.hostname + "/postcard/editmessage/" + orderId;
                    output(url);
                    self.location = url;
                }
            },
            "json"
        );

    }
});



