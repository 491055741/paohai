
var orderId = '0';

$(document).on("pageinit", "#completePage", function() {
    output("completePage init");
    orderId = $('#orderId').val();
});

$(document).on("pageshow", "#completePage", function() {
    output("completePage show");
    var postcardurl = "http://" + window.location.host + "/postcard/shareimage/" + orderId;
    bShare.addEntry({
        title: "我的趣邮明信片",
        url: postcardurl,
        summary: "",
        pic: postcardurl
    });
});

