(function($) {
    var domain = "http://" + window.location.host;
    var isShowImageFace = true;
//    var userImage = new Image();
    var userImage;
    $(function() {

        initOrder();

        $("#cancel-btn").fastClick(function() {
            var nonce = new Date().getTime();
            var url = "http://" + window.location.host + "/postcard/orderlist?userName=" + $('#var-user-name').val() + "&nonce=" + nonce;
            window.location = url;
        });

        $("#confirm-btn").fastClick(function() {
            var nonce = new Date().getTime();
            var url = "http://" + window.location.host + "/postcard?picurl=" + $('#var-user-picurl').val() + "&nonce=" + nonce;
            window.location = url;
        });

        $(window).on("orientationchange", function() {
            HC.checkOrientation();
        });

        HC.loadingClose();
        HC.checkOrientation();
    });

    function initOrder() {
        order.setOrderId($("#var-order-id").val());
        order.getPostcard().setPostmarkIndex($("#var-postmark-index").val());
        order.getPostcard().getImage().setVars({
            url          : $("#var-user-picurl").val(),
            templateIndex: $("#var-template-index").val(),
            offsetX      : $("#var-offset-x").val(),
            offsetY      : $("#var-offset-y").val()
        });
        order.getPostcard().getReceiptAddress().setVars({
            name   : $("#var-recipient").val(),
            address: $("#var-address").val(),
            zipcode: $("#var-zipcode").val()
        });
        order.getPostcard().getMessage().setVars({
            salutation: $("#var-salutation").val(),
            content   : $("#var-message").val(),
            signature : $("#var-signature").val()
        });

        var selectedTemplateIndex = order.getPostcard().getImage().getTemplateIndex();
        var frameImg = document.getElementsByClassName('bgLayer_img_a')[0];
        frameImg.src = $("#var-template").data("thumb");

        userImage = document.getElementById("previewUserImg");
        userImage.onload = function() {
            initPreview();
        }
        userImage.src = order.getPostcard().getImage().getUrl();
    }

    function initPreview() {

        var frameImg = document.getElementsByClassName('bgLayer_img_a')[0];

        var pic_orig_w = userImage.width,
            pic_orig_h = userImage.height,
            bg_w = frameImg.offsetWidth,
            bg_h = frameImg.offsetHeight;

        var a = pic_orig_w, b = pic_orig_h;
        var selectedTemplateIndex = order.getPostcard().getImage().getTemplateIndex();
        var isRotate = ($("#var-template").data("rotate") == "-90");
        var imageOffsetX = order.getPostcard().getImage().getOffsetX();
        var imageOffsetY = order.getPostcard().getImage().getOffsetY();

        if (isRotate) {
            var temp = a; a = b; b = temp;
            $("#previewUserImg").addClass('img_rotate');
        }
        var wRatio = bg_w / a;
        var hRatio = bg_h / b;
        var ratio = wRatio > hRatio ? wRatio : hRatio;
        var pic_w = a * ratio;
        var pic_h = b * ratio;

        var imgLayer_a = document.getElementsByClassName('imgLayer_a')[0];

        var bgLayer_a = document.getElementsByClassName('bgLayer_a')[0];
        EventUtil.addhandler(bgLayer_a,"touchmove", handtouch);

        imgLayer_a.scrollLeft = (-imageOffsetX * pic_w);
        imgLayer_a.scrollTop = (-imageOffsetY * pic_h);

        if (isRotate) {
            temp = pic_w; pic_w = pic_h; pic_h = temp;
        }

        $('#previewUserImg').css({
            width: pic_w,
            height: pic_h
        });
    }

    function handtouch (e) {
        switch(e.type){
            case "touchmove":
                e.preventDefault();
                break;
        }
    }



})(jQuery);


