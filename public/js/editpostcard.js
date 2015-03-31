/**
 * Created by admin on 2014/11/6.
 */
(function($) {
    var receiptInfo = order.getPostcard().getReceiptAddress();
    var messageInfo = order.getPostcard().getMessage(); 
    var isMediaAvailable = true;
    //if ($("#var-partner-id").val()) {
    //    isMediaAvailable = false;
    //}
    if ( ! isMediaAvailable) {
        $(".pop3 .voice_btn").hide();
    }

    function setCardInfo() {
        var postmarkIndex = order.getPostcard().getPostmarkIndex();

        //页面2中要初始化的信息
        $(".shoujianInput").val(receiptInfo.getName());
        //祝福信息
        $("#liuyan").val(messageInfo.getContent());
        //邮戳
        if (postmarkIndex !== "") {
            $("#post_stamp").attr("src", "/images/postmark/small/youchuo"+ postmarkIndex +".png").show();
        } else {
            $("#post_stamp").hide();
        }

        //邮编回填
        var zipcode = receiptInfo.getZipcode() || "000000";//邮政编码
        var zipcodeArr = String(zipcode).split("");
        $(".youzhen em").each(function() {
            $(this).text(zipcodeArr.shift());
        });

        //弹窗1
        if (postmarkIndex != "") {
            $(".pop1 [data-index]").removeClass("on").eq(postmarkIndex).addClass("on")
        } else {
            $(".pop1 [data-index]").removeClass("on");
        }

        //弹窗2
        $(".pop2 .recipient_input").val(receiptInfo.getName());
        $(".pop2 .postcode_input").val(receiptInfo.getZipcode());

        var address = receiptInfo.getAddress()
        address = LocalitySelection.selectWithAddress(address);
        $(".pop2 .address_input").val(address);

        //弹窗3
        $(".pop3 .recipient_input").val(messageInfo.getSalutation());
        $(".pop3 .liuyan").val(messageInfo.getContent());
        $(".pop3 .myName").val(messageInfo.getSignature());
    }

    function callPop() {
        var postmarkIndex = order.getPostcard().getPostmarkIndex();
        var isVoiceTipShown = isMediaAvailable ? false : true;
        $("#memory-stamp-button").on("click", function() { //显示弹窗1
            $(".pop1").show();
            setCardInfo();
        });
        $("#latlng-button").on("click", function() { //显示弹窗1
            // Latlng
            order.getUserLnglat();
        });
        $(".shoujianInput").on("click", function() { //显示弹窗2
            $(".pop2").show().find(".recipient_input").trigger("focus");
        });
        $(".voice-tips").on("click" , function() {
            $(".voice-tips").hide();
            $(".pop3").show().find(".recipient_input").trigger("focus");
            isVoiceTipShown = true;
        });
        $("#liuyan").on("click", function() { //显示弹窗3
            if (isVoiceTipShown) {
                $(".pop3").show().find(".recipient_input").trigger("focus");
            } else {
                $(".voice-tips").show();
            }
        });

        $(".pop1 [data-index]").on("click", function() {
            var isChosen = $(this).hasClass("on");
            var postmarkIndex = "";
            $(".pop1 [data-index]").removeClass("on");
            if ( ! isChosen) {
                $(this).addClass("on");
                postmarkIndex = $(this).data("index");
            }
            // Set postcard object
            order.getPostcard().setPostmarkIndex(postmarkIndex);
        });
        $("#pop1_conf").on("click", function() { //确认按钮: 邮戳弹窗
            $(".pop1").hide();
            setCardInfo();
        });

        $("#street_detail").on("blur", function () {
            $.get(
                "http://" + window.location.host + "/contact/postcode",
                {
                    address: $(".pop2 .province_input").val()
                    + $(".pop2 .city_input").val() + $("#street_detail").val()
                },
                function success(data) {
                    if (data.code != 0) {
                        alert("获取邮编失败");
                        return;
                    }

                    if (data.data) {
                        $(".postcode_input").val(data.data);
                    }

                    console.log(data);
                },
                "json"
            );
        });

        $("#pop2_conf").on("click", function() { //确认按钮: 收件人信息弹窗
            // Set postcard object
            if ($(".pop2 .province_input").val() == "省份" || $(".pop2 .city_input").val() == "城市") {
                HC.showError("请选择省/市/区");
                return;
            }

            // 天府童星活动
            if ($("#var-activity-id").val() == 104 && !$(".pop2 .mobile_input").val()) {
                HC.showError("请填写手机号");
                return;
            }

            receiptInfo.setVars({
                name: $(".pop2 .recipient_input").val(),
                address: $(".pop2 .province_input").val()
                    + $(".pop2 .city_input").val()
                    + $(".pop2 .address_input").val(),
                zipcode: $(".pop2 .postcode_input").val(),
                mobile:  $(".pop2 .mobile_input").val()
            });
            var errMsg = HC.checkAddress(receiptInfo);
            if (errMsg) {
                HC.showError(errMsg);
                return;
            }

            if ($(".pop2 .save_add").hasClass("in")) {
                order.saveContact();
            }
            $(".pop2").hide();
            setCardInfo();
        });
        $("#pop3_conf").on("click", function() { //确认按钮: 祝福信息弹窗
            // Set postcard object
            messageInfo.setVars({
                salutation: $(".pop3 .recipient_input").val(),
                content: $(".pop3 .liuyan").val(),
                signature: $(".pop3 .myName").val()
            });
            $(".pop3").hide();
            setCardInfo();
        });

        var voiceMediaId = $('#var-voice-media-id').val();
        if (!voiceMediaId || voiceMediaId == '0') {
            $(".pop3 .play_voice_btn").css("display","none");
        } else {
            $(".pop3 .myName").css({width: 130});
//            $("#voiceMessageButton").attr({'src': '/images/small/rerecord_voice_btn.png'});
        }

        $(".pop3 .voice_btn").on("click", function() { //语音留言按钮
            messageInfo.setVars({
                salutation: $(".pop3 .recipient_input").val(),
                content: $(".pop3 .liuyan").val(),
                signature: $(".pop3 .myName").val(),
            });
            order.requestVoice();
        });
        $(".pop3 .play_voice_btn").on("click", function() {
            var url = 'http://' + window.location.host + '/postcard/voice?mediaId=' + voiceMediaId + "&nonce=" + HC.getNonceStr();
            var audio = document.createElement("audio");
            if (audio != null && audio.canPlayType && audio.canPlayType("audio/mpeg")) {
                audio.src = url;
                audio.play();
            }
        });
        /********** address book **************/
        $(".pop2 .save_add").on("click", function() { // 存入地址
            $(this).toggleClass("in");
            /*
            receiptInfo.setVars({
                name: $(".pop2 .recipient_input").val(),
                address: $(".pop2 .to_address").val(),
                zipcode: $(".pop2 .postcode").val(),
            });
            order.saveContact();
            */
        });
        $(".pop2 .go_add").on("click", function() { // 唤起地址簿按钮
            order.getContacts(function(contacts) {
                $("#address-book .list-ul-hc").empty();
                if (contacts.length > 0) {
                    $.each(contacts, function(index, item) {
                        var line = $("#address-template ul").clone();
                        line.find("span.name-hc").text(item.contactName);
                        line.find("span.addr-hc").text(item.address);
                        line.find("span.post-hc").text(item.zipCode);
                        $("#address-book .list-ul-hc").append(line);
                    });
                }
                $("#address-book").show();
            });

        });
        $(document).on("click", "#address-book a.sel-btn-hc", function() { // 选中
            $("#address-book a.sel-btn-hc").removeClass("on");
            $(this).addClass("on");
        });
        $("#close-address").on("click", function() { // 关闭
            $("#address-book").hide();
        });
        $("#sure-address").on("click", function() { // 确认
            var chosenAddressDom = $("#address-book a.on").parents("ul");
            if (chosenAddressDom.size() != 0) {
                receiptInfo.setVars({
                    name: chosenAddressDom.find("span.name-hc").text(),
                    address: chosenAddressDom.find("span.addr-hc").text(),
                    zipcode: chosenAddressDom.find("span.post-hc").text(),
                });
            }
            $("#address-book").hide();
            setCardInfo();
        });
    }

    $(function() {
        LocalitySelection.initSelect();
        $("#province_select").change(function(){LocalitySelection.select();});

        // init data
        order.setOrderId($("#var-order-id").val())
            .setUserName($("#var-user-name").val());
        order.getPostcard().setPostmarkIndex($("#var-postmark-index").val());

        order.getPostcard().getReceiptAddress().setVars({
            name: $("#var-recipient").val(),
            address: $("#var-address").val(),
            zipcode: $("#var-zipcode").val(),
            mobile:  $("#var-mobile").val()
        });
        order.getPostcard().getMessage().setVars({
            salutation: $("#var-salutation").val(),
            content: $("#var-message").val(),
            signature: $("#var-signature").val(),
        });
        callPop();

        if ($("#var-activity-id").val() == 102) {  // 长风情人节活动
            order.getPostcard().setPostmarkIndex('111');
            $("#memory-stamp-button").hide();
            $("#latlng-button").hide();
        } else if ($("#var-activity-id").val() == 104) {  // 天府童星活动
            order.getPostcard().setPostmarkIndex('112');
            $("#memory-stamp-button").hide();
            $("#latlng-button").hide();
        }

        setCardInfo();

        $("#next-step").on("click", function() {
            if (order.getPostcard().getReceiptAddress().isComplete()) {
                order.updateOrderAfterEdit();
                return;
            } else {
                HC.showError("亲，您还没有填写收件人信息哦");
            }
        });
        $("#prev-step").on("click", function() {
            order.goToStepOne();
        });
        $(window).on("orientationchange", function() {
            HC.checkOrientation();
        });
        HC.loadingClose();
        HC.checkOrientation();
        HC.addBridgeListener();
    });
})(jQuery);
