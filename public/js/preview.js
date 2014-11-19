(function($) {
    var domain = "http://" + window.location.host;
    var isShowImageFace = true;
    var userImage = new Image();
    $(function() {

        initOrder();

        $("#gotoPayButton").fastClick(function(){
            jsApiCall();
            var url = "http://" + window.location.host + "/wxpay/asyncmakepicture/" + order.getOrderId();
            $.get(
                url,
                function success(data) {
                }
            );
        });

        $("#editButton").fastClick(function(){
            HC.goToPage(domain + "/postcard/index?orderId=" + order.getOrderId() + "&nonce=" + HC.getNonceStr());
        });

        $("#toggleFaceButton").fastClick(function(){
            toggleFace();
        });

        $("#prev-step").on("click", function() {
            order.goToStepTwo();
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
            offsetY      : $("#var-offset-y").val(),
        });
        order.getPostcard().getReceiptAddress().setVars({
            name   : $("#var-recipient").val(),
            address: $("#var-address").val(),
            zipcode: $("#var-zipcode").val()
        });
        order.getPostcard().getMessage().setVars({
            salutation: $("#var-salutation").val(),
            content   : $("#var-message").val(),
            signature : $("#var-signature").val(),
        });

        var selectedTemplateIndex = order.getPostcard().getImage().getTemplateIndex();
        var frameImg = document.getElementsByClassName('bgLayer_img_a')[0];
        frameImg.src = "/images/small/template"+selectedTemplateIndex+".png";

        // userImage = document.getElementsByClassName('imgLayer_img_a')[0];
        userImage.onload = function() {
            initPreview();
        }
        userImage.src = order.getPostcard().getImage().getUrl();
    }

    function initPreview() {

        var frameImg = document.getElementsByClassName('bgLayer_img_a')[0];
        var imageLayer = document.getElementsByClassName('imgLayer_a')[0];

        var pic_orig_w = userImage.width,
            pic_orig_h = userImage.height,
            bg_w = frameImg.offsetWidth,
            bg_h = frameImg.offsetHeight;

        var a = pic_orig_w, b = pic_orig_h;
        var selectedTemplateIndex = order.getPostcard().getImage().getTemplateIndex();
        var isRotate = (selectedTemplateIndex >= 6);
        var imageOffsetX = order.getPostcard().getImage().getOffsetX();
        var imageOffsetY = order.getPostcard().getImage().getOffsetY();
        if (isRotate) {
            temp = a; a = b; b = temp;
        }
        var wRatio = bg_w / a;
        var hRatio = bg_h / b;
        var ratio = wRatio > hRatio ? wRatio : hRatio;
        var pic_w = a * ratio;
        var pic_h = b * ratio;

        var canvas = document.getElementById('previewUserImg');
        canvas.width = pic_w;
        canvas.height = pic_h;
        var ctx = canvas.getContext('2d');
        if (!isRotate) {
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
        $('#previewUserImg').css({
            left: imageOffsetX * pic_w,
            top: imageOffsetY * pic_h,
        });

        $("#salutationPreview").text(order.getPostcard().getMessage().getSalutation());
        $("#messagePreview").text(order.getPostcard().getMessage().getContent());
        $("#addressPreview").text(order.getPostcard().getReceiptAddress().getAddress());
        $("#zipcodePreview").text(order.getPostcard().getReceiptAddress().getZipcode());
        $("#signaturePreview").text('－' + order.getPostcard().getMessage().getSignature());
        $("#recipientPreview").text(order.getPostcard().getReceiptAddress().getName());

        if ($("#var-voice-media-id").val() != "") {
            $("#qrImagePreview").css("display","inline");
        }
        if (order.getPostcard().getPostmarkIndex() != "") {
            $("#postmarkPreview").css("display","inline");
            $("#postmarkPreview").attr("src","/images/postmark/small/youchuo"+order.getPostcard().getPostmarkIndex()+".png");
        } else if ($("#var-city").val() != '0') {
            $("#postmarkPreview").css("display","inline");
            $("#postmarkPreview").attr("src","/images/postmark/small/postmark_location.png");
        }
    }

    function toggleFace() {
        isShowImageFace = !isShowImageFace;
        $("#textFace").css("display",isShowImageFace ? "none" : "");
        $("#imageFace").css("display",isShowImageFace ? "" : "none");
    }

})(jQuery);

