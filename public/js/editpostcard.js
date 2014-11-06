/**
 * Created by admin on 2014/11/6.
 */
(function($) {
    var receiptInfo = order.getPostcard().getReceiptAddress();
    var messageInfo = order.getPostcard().getMessage(); 

    function setCardInfo() {
        var postmarkIndex = order.getPostcard().getPostmarkIndex();

        //页面2中要初始化的信息
        $(".shoujianInput").val(receiptInfo.getName());
        //祝福信息
        $("#liuyan").val(messageInfo.getContent());
        //邮戳
        $("#post_stamp").attr("src", "/images/postmark/youchuo"+ postmarkIndex +".png");

        //邮编回填
        var zipcode = receiptInfo.getZipcode() || "000000";//邮政编码
        var zipcodeArr = String(zipcode).split("");
        $(".youzhen em").each(function() {
            $(this).text(zipcodeArr.shift());
        });

        //弹窗1
        $(".pop1 [data-index]").removeClass("on").eq(postmarkIndex).addClass("on")


        //弹窗2
        $(".pop2 .to_who").val(receiptInfo.getName());
        $(".pop2 .postcode").val(receiptInfo.getZipcode());
        $(".pop2 .to_address").val(receiptInfo.getAddress());

        //弹窗3
        $(".pop3 .to_who").val(messageInfo.getSalutation());
        $(".pop3 .liuyan").val(messageInfo.getContent());
        $(".pop3 .myName").val(messageInfo.getSignature());
    }

    function callPop() {
        var postmarkIndex = order.getPostcard().getPostmarkIndex();

        $("#memory-stamp-button").on("click", function() { //显示弹窗1
            $(".pop1").show();
            setCardInfo();
        });
        $("#latlng-button").on("click", function() { //显示弹窗1
            // TODO latlng
            $(".pop1").show();
        });
        $(".shoujianInput").on("click", function() { //显示弹窗2
            $(".pop2").show();
        });
        $("#liuyan").on("click", function() { //显示弹窗3
            $(".pop3").show();
        });

        $(".pop1 [data-index]").on("click", function() {
            $(".pop1 [data-index]").removeClass("on");
            $(this).addClass("on");
            // Set postcard object
            order.getPostcard().setPostmarkIndex($(this).data("index"));
        });
        $("#pop1_conf").on("click", function() { //确认按钮: 邮戳弹窗
            $(".pop1").hide();
            setCardInfo();
        });
        $("#pop2_conf").on("click", function() { //确认按钮: 收件人信息弹窗
            // Set postcard object
            receiptInfo.setVars({
                name: $(".pop2 .to_who").val(),
                address: $(".pop2 .to_address").val(),
                zipcode: $(".pop2 .postcode").val(),
            });
            $(".pop2").hide();
            setCardInfo();
        });
        $("#pop3_conf").on("click", function() { //确认按钮: 祝福信息弹窗
            // Set postcard object
            messageInfo.setVars({
                salutation: $(".pop3 .to_who").val(),
                content: $(".pop3 .liuyan").val(),
                signature: $(".pop3 .myName").val(),
            });
            $(".pop3").hide();
            setCardInfo();
        });
    }

    $(function() {
        // init data
        order.setOrderId($("#var-order-id").val());
        order.getPostcard().setPostmarkIndex($("#var-postmark-index").val());
        order.getPostcard().getReceiptAddress().setVars({
            name: $("#var-recipient").val(),
            address: $("#var-address").val(),
            zipcode: $("#var-zipcode").val(),
        });
        order.getPostcard().getMessage().setVars({
            salutation: $("#var-salutation").val(),
            content: $("#var-message").val(),
            signature: $("#var-signature").val(),
        });
        callPop();
        setCardInfo();

        $("#next-step").on("click", function() {
            order.updateOrderAfterEdit();
        });
    });
})(jQuery);
