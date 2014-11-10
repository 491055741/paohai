(function($) {
    EventUtil.addLoadEvent(HC.init);
    EventUtil.addhandler(HC.touch.bg_layer,"touchstart",HC.handtouch);
    EventUtil.addhandler(HC.touch.bg_layer,"touchmove",HC.handtouch);

    $(function() {
        // init order
        $("#next-step").on("click", function() {
            order.setOrderId($("#var-order-id").val())
                .setUserName($("#var-user-name").val());
            order.getPostcard().getImage().setVars({
                url: $("#var-user-picurl").val(),
                templateIndex: $("#var-template-index").val(),
                offsetX: $("#var-offset-x").val(),
                offsetY: $("#var-offset-y").val(),
            });

            console.log(order);
            if (order.getOrderId() == "0") {
                order.placeOrder(); 
            } else {
                order.updateImageForOrder();
            }
        });
        HC.loadingClose();
    });


})(jQuery);
