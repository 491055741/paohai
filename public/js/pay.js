// var orderId = '0';

$(document).on("pageinit", "#paymentPage", function() {

    output("paymentPage init");
    // orderId = $('#orderId').val();

    // $("#submitPaymentButton1").fastClick(function() {
    //     submitPayment(false);
    // });

    // $("#submitPaymentButton2").fastClick(function() {
    //     submitPayment(true);
    // });

});

// function submitPayment(isXingYeBank) {
//     var bank = isXingYeBank? 'XingYe':'other';
//     var url = 'http://' + window.location.host + '/wxpay/pay?orderId=' + orderId + '&bank=' + bank;
//     self.location = url;
// }

