
var orderId = '0';

function init() {
    $.mobile.changePage($("#completePage"), {
        transition: "none"
    });
}

$(function() {

    $("#completePage").on("pageinit", function() {
        output("completePage init");
        orderId = $('#orderId').val();
    });

    $("#completePage").on("pageshow", function() {
        output("completePage show");
        var postcardurl = "http://" + window.location.host + "/postcard/preview/" + orderId;
        bShare.addEntry({
            title: "我的泡海明信片",
            url: postcardurl,
            summary: "",
            pic: postcardurl
        });
    });
});

