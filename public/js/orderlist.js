(function($) {
    var domain = "http://" + window.location.host;
    var isInited = false;

    function showOrderList() {   // 显示地址簿
        var self = this;

        if ( ! isInited) {
            // Event
            $(document).on("click", ".list-wrap-hc .addr-title", function() {
                var currentAddress = $(this).parents(".list-info-ab");
                if (currentAddress.hasClass("on")) {
                    currentAddress.removeClass("on").find("ul").hide();
                } else {
                    $(".list-wrap-hc .list-info-ab").removeClass("on").find("ul").hide();
                    currentAddress.addClass("on").find("ul").show();
                }
            });
            $(document).on("click", ".list-wrap-hc .share_hc", function() {
                var addressObj = $(this).parents(".list-info-ab");
                var contactName = addressObj.find(".addr-name").text();
                self.deleteContact(contactName, function() {
                    addressObj.remove();
                });
            });
            $(document).on("click", ".list-wrap-hc .repeatorder_hc", function() {
//                        var addressObj = $(this).parents(".list-info-ab");
//                        $("#pop-address")
//                            .find(".recipient_input").val(addressObj.find(".addr-name").text()).end() // attr("disabled", true).
//                            .find(".postcode_input").val(addressObj.find(".addr-post").text()).end()
//                            .find(".address_input").val(addressObj.find(".addr-addr").text()).end()
//                            .show();
                var repeatOrderLink = domain + '/postcard/repeatorder/';
                window.location = repeatOrderLink;
            });

            isInited = true;
        }
    }

    $(function() {
        showOrderList();
    });

})(jQuery);
