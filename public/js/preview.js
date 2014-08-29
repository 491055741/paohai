var orderId   = '';
var userImage = new Image();
var canvas_w  = 262;
var canvas_h  = 397;
var pic_orig_w = 0;
var pic_orig_h = 0;
var userPicUrl = '';

var salutation = '';
var message    = '';
var signature  = '';

var recipient = '';
var address   = '';
var zipcode   = '';
var mobile    = '';


$(document).on("pageinit", "#previewPage", function() {

    output("previewPage init");
    orderId    = $('#orderId').val();
    userPicUrl = $('#picUrl').val();
    salutation = $('#salutation').val();
    message    = $('#message').val();
    signature  = $('#signature').val();
    recipient  = $('#recipient').val();
    address    = $('#address').val();
    zipcode    = $('#zipcode').val();
    mobile     = $('#mobile').val();

    userImage.onload = function() {
        pic_orig_w = userImage.width;
        pic_orig_h = userImage.height;
        initPreview();
    }

    userImage.src = userPicUrl;
    // $('#previewUserImg').shadow();
    $("#gotoPayButton").fastClick(function() {
        gotoPayPage();
    });

    $("#editButton").fastClick(function() {
        gotoEditPage();
    });
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
    var pic_w = a * ratio;
    var pic_h = b * ratio;

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
        top: imageOffsetY * pic_h,
    });

    $("#messagePreview").text(message);
    $("#addressPreview").text(address);
    $("#zipcodePreview").text(zipcode);
    $("#signaturePreview").text('－' + signature);
    $("#recipientPreview").text(recipient + ' ' + mobile);
}

function gotoEditPage() {
    var url = "http://" + window.location.host + "/postcard/index?orderId=" + orderId + "&nonce=" + getNonceStr();
    self.location = url;        
}

function gotoPayPage() {
    var url = "http://" + window.location.host + "/wxpay/pay?orderId=" + orderId + "&nonce=" + getNonceStr();;
    self.location = url;        
}
