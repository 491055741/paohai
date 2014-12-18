(function($) {
    orderId = $('#orderId').val();
    $(function() {
        var postcardurl = "http://" + window.location.host + "/postcard/shareimage/" + orderId;
        bShare.addEntry({
            title: "我的趣邮明信片",
            url: postcardurl,
            summary: "",
            pic: postcardurl
        });
    })
})(jQuery);
