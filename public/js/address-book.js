(function($) {
    var domain = "http://" + window.location.host;

    var imgUrl = 'https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoib04c8tvEHviaFSFtLGJ5Ox1H9CibIfOiauH0UEiaso13g5zgJ5E8SozibwIibESViaXMQ5keYwQAZwHLylw/0';
    var descContent = '亲，您的好友在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦';
    var shareTitle = '我在趣邮向您索要收件地址';
    var appid = 'wxbd6694a085209f4d';
    var userName = $('#var-user-name').val();
    var shareLink = domain + '/contact/filladdress?userName=' + userName;

    /*************** Contacts begin ***********************/
    function Address() {
        this.varCollection = {
            name: "",
            address: "",
            zipcode: ""
        };
    }

    $.extend(Address.prototype, {
        isComplete: function() {
            if ( ! this.varCollection.name || ! this.varCollection.address || ! this.varCollection.zipcode) {
                return false;
            }
            return true;
        },
        setVars: function(paramData) {
            $.extend(this.varCollection, paramData);
            return this;
        },
        getName: function() {
            return this.varCollection.name;
        },
        setName: function(name) {
            this.varCollection.name = name;
            return this;
        },
        getAddress: function() {
            return this.varCollection.address;
        },
        setAddress: function(address) {
            this.varCollection.address = address;
            return this;
        },
        getZipcode: function() {
            return this.varCollection.zipcode;
        },
        setZipcode: function(zipcode) {
            this.varCollection.zipcode = zipcode;
            return this;
        }
    });

    function Contacts() {
        var userName = "";
        var addressBook = [];
        var chosedIndex = 0;                // 被选中的地址序号
        var isInited = false;

        $.extend(Contacts.prototype, {
            setUserName: function(name) {
                userName = name;
                return this;
            },
            saveContact: function(address, callback) { // address为Address对象
                var self = this;
                var url = domain + "/contact/save";
                var params = {
                    userName: userName,
                    contactName: address.getName(),
                    address: address.getAddress(),
                    zipCode: address.getZipcode(),
                };
                $.ajax({
                    url: url,
                    type: "POST",
                    data: params,
                    dataType: "json",
                    timeout: 10000,
                }).done(function(data) {
                    self.fetchContacts();
                }).fail(function(xmlhttprequest, err, e) {
                    if (err == "timeout") {
                        HC.showError("网速不给力，请稍候再试哦");
                    } else {
                        HC.showError("add contact failed !");
                    }
                });

                return this;
            },
            fetchContacts: function() {
                self = this;
                var url = domain + "/contact/listcontacts?userName=" + userName;
                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    timeout: 10000,
                }).done(function(data) {
                    addressBook = [];
                    if (data.code != "0") {
                        HC.showError(data.msg, data.code);
                        return self;
                    }
                    $.each(data.data, function(i, v) {
                        var address = new Address();
                        address.setName(v.contactName)
                            .setAddress(v.address)
                            .setZipcode(v.zipCode);
                        addressBook.push(address);
                    });
                    self.showAddressBook();
                }).fail(function(xmlhttprequest, err, e) {
                    if (err == "timeout") {
                        HC.showError("网速不给力，请稍候再试哦");
                    } else {
                        HC.showError("add contact failed!");
                    }
                });

                return this;
            },
            deleteContact: function(contactName, callback) {
                var self = this;
                var url = domain + "/contact/delete";
                var params = {
                    userName: userName,
                    contactName: contactName,
                };
                $.ajax({
                    url: url,
                    type: "POST",
                    data: params,
                    dataType: "json",
                    timeout: 10000,
                }).done(function(data) {
                    callback();
                }).fail(function(xmlhttprequest, err, e) {
                    if (err == "timeout") {
                        HC.showError("网速不给力，请稍候再试哦");
                    } else {
                        HC.showError("add contact failed !");
                    }
                });

                return this;
            },
            showAddressBook: function() {   // 显示地址簿
                var self = this;
                $(".popbox, #pop-address").hide();
                $("div.address-book").empty();
                $.each(addressBook, function(index, address) {
                    var addrObj = $("#address-template div").clone();
                    addrObj.find(".addr-title").text(address.getName()).css("cursor", "pointer").end()
                        .find(".addr-name").text(address.getName()).end()
                        .find(".addr-addr").text(address.getAddress()).end()
                        .find(".addr-post").text(address.getZipcode()).end()
                        .appendTo("div.address-book");
                });

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
                    $(document).on("click", ".list-wrap-hc .delete_hc", function() {
                        var addressObj = $(this).parents(".list-info-ab");
                        var contactName = addressObj.find(".addr-name").text();
                        self.deleteContact(contactName, function() {
                            addressObj.remove();
                        });
                    });
                    $(document).on("click", ".list-wrap-hc .edit_hc", function() {
                        var addressObj = $(this).parents(".list-info-ab");
                        $("#pop-address")
                            .find(".recipient_input").val(addressObj.find(".addr-name").text()).end() // attr("disabled", true).
                            .find(".postcode_input").val(addressObj.find(".addr-post").text()).end()
                            .find(".address_input").val(addressObj.find(".addr-addr").text()).end()
                            .show();
                    });
                    $(document).on("click", "#pop-cancel", function() {
                        $("#pop-address").hide();
                    });
                    $(document).on("click", "#pop-confirm", function() {
                        var address = new Address();
                        address.setVars({
                            "name": $("#pop-address .recipient_input").val(),
                            "address": $("#pop-address .province_input").val()
                                       + $("#pop-address .city_input").val()
                                       + $("#pop-address .district_input").val()
                                       + $("#pop-address .address_input").val(),
                            "zipcode": $("#pop-address .postcode_input").val(),
                        });
                        var msg = HC.checkAddress(address);
                        if (msg) {
                            HC.showError(msg);
                            return;
                        }

                        self.saveContact(address)
                    });
                    $(document).on("click", "#add-contact", function() {
                        $("#pop-address")
                            .find(".recipient_input").val("").attr("disabled", false).end()
                            .find(".postcode_input").val("").end()
                            .find(".address_input").val("").end()
                            .find(".mobile_input").val("").end()
                            .find(".province_input").val("").end()
                            .find(".city_input").val("").end()
                            .find(".district_input").val("").end()
                            .show();
                    });
                    $(document).on("click", "#share-contact", function() {
                        $("#sharetips").show();
                    });
                    $("#sharetips").fastClick(function() {
                        $("#sharetips").hide();
                    });
                    isInited = true;
                }
            }
        });
    }

    function shareFriend() {
        WeixinJSBridge.invoke('sendAppMessage',{
            "appid": appid,
            "img_url": imgUrl,
            "img_width": "640",
            "img_height": "640",
            "link": shareLink,
            "desc": descContent,
            "title": shareTitle
        }, function(res) {
            _report('send_msg', res.err_msg);
        })
    }

    function shareTimeline() {
        WeixinJSBridge.invoke('shareTimeline',{
            "img_url": imgUrl,
            "img_width": "640",
            "img_height": "640",
            "link": shareLink,
            "desc": descContent,
            "title": shareTitle
        }, function(res) {
            _report('timeline', res.err_msg);
        });
    }

    function onBridgeReady() {
        WeixinJSBridge.on('menu:share:appmessage', function(argv){  // 好友
            shareFriend();
        });
        WeixinJSBridge.on('menu:share:timeline', function(argv){ // 朋友圈
            shareTimeline();
        });
    }

    var addressBook = new Contacts();
    $(function() {
        addressBook.setUserName($("#var-user-name").val())
            .fetchContacts();

        if (typeof WeixinJSBridge == "undefined") {
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        } else {
            onBridgeReady();
        }
    });

})(jQuery);
