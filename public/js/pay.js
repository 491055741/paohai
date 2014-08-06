
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
        var url = 'http://' + window.location.host + '/wxpay/pay/' + orderId + '?bank=' + bank;
        self.location = url;
    }
});
