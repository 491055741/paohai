(function($) {

$(function() {
    // init order
    order.setOrderId($("#var-order-id").val())
        .setUserName($("#var-user-name").val());
    var postcard = order.getPostcard();
    postcard.getImage()
        .setUrl($("#var-user-picurl").val())
        .setTemplateIndex($("#var-template-init").val())
        .setOffsetX($("#var-offset-x").val())
        .setOffsetY($("#var-offset-y").val());
});

    function ImageAction() {

    }
    $.extend(ImageAction.prototype, {
        show: function() {

        },
        changeTemplate: function() {

        },
    });


})(jQuery);
