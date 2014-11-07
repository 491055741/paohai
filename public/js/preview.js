(function($) {
    var domain = "http://" + window.location.host;
    var isShowImageFace = true;
    $(function() {

        initOrder();
        initPreview();

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
    }

    function initPreview() {

        var userImg = document.getElementsByClassName('imgLayer_img_a')[0];
        var frameImg = document.getElementsByClassName('bgLayer_img_a')[0];
        var imageLayer = document.getElementsByClassName('imgLayer_a')[0];

        var pic_orig_w = userImg.offsetWidth,
            pic_orig_h = userImg.offsetHeight,
            bg_w = frameImg.offsetWidth,
            bg_h = frameImg.offsetHeight;

        var a, b;
        a = pic_orig_w;
        b = pic_orig_h;
        var selectedTemplateIndex = order.getPostcard().getImage().getTemplateIndex();
        var imageOffsetX = order.getPostcard().getImage().getOffsetX();
        var imageOffsetY = order.getPostcard().getImage().getOffsetY();
        if (selectedTemplateIndex > 6) {
            temp = a; a = b; b = temp;
        }
        var wRatio = bg_w / a;
        var hRatio = bg_h / b;
        var ratio = wRatio > hRatio ? wRatio : hRatio;
        var pic_w = a * ratio;
        var pic_h = b * ratio;

        userImg.style.width = pic_w + "px";
        userImg.style.height = pic_h + "px";
        var left = -parseFloat(imageOffsetX) * pic_w;
        imageLayer.scrollLeft = parseInt(left);
        imageLayer.scrollTop = (-parseFloat(imageOffsetY) * pic_h);
        frameImg.src = "/images/small/template"+selectedTemplateIndex+".png";

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
        }
    }

    function toggleFace() {
        isShowImageFace = !isShowImageFace;
        $("#textFace").css("display",isShowImageFace ? "none" : "");
        $("#imageFace").css("display",isShowImageFace ? "" : "none");
    }

})(jQuery);

