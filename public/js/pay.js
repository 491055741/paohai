
var orderId = '0';

function init() {
    $.mobile.changePage($("#paymentPage"), {
        transition: "none"
    });
}

$(function() {

    $("#paymentPage").on("pageinit", function() {

        output("paymentPage init");
        orderId = $('#orderId').val();

        $("#submitPaymentButton1").fastClick(function() {
            submitPayment(false);
        });

        $("#submitPaymentButton2").fastClick(function() {
            submitPayment(true);
        });

    });

    function submitPayment(isXingYeBank) {
        var bank = isXingYeBank? 'XingYe':'other';
        var url = 'http://' + window.location.hostname + '/wxpay/pay/' + orderId + '?bank=' + bank;
        self.location = url;
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
        var postcardurl = "http://" + window.location.hostname + "/postcard/preview/" + orderId;
        bShare.addEntry({
            title: "我的泡海明信片",
            url: postcardurl,
            summary: "",
            pic: postcardurl
        });

    });

});

