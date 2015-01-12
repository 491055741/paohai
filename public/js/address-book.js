(function($) {
    var domain = "http://" + window.location.host;

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

                        if ($("#pop-address .province_input").val() == "省份" || $("#pop-address .city_input").val() == "城市") {
                            HC.showError("请选择省/市/区");
                            return;
                        }

                        var address = new Address();
                        address.setVars({

                            "name": $("#pop-address .recipient_input").val(),
                            "address": $("#pop-address .province_input").val()
                                       + $("#pop-address .city_input").val()
                                       + $("#pop-address .district_input").val()
                                       + $("#pop-address .address_input").val(),
                            "zipcode": $("#pop-address .postcode_input").val()
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


    function onBridgeReady() {

//        WeixinJSBridge.call('hideOptionMenu');
//        return;

        var imgUrl = 'https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoib04c8tvEHviaFSFtLGJ5Ox1H9CibIfOiauH0UEiaso13g5zgJ5E8SozibwIibESViaXMQ5keYwQAZwHLylw/0';
        var shareTitle = '我在趣邮向您索要收件地址';
        var userName = $('#var-user-name').val();
        var nickName = $('#var-nick-name').val();
        var descContent = nickName.length > 0 ? '亲，您的好友['+nickName+']在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦' : '亲，您的好友在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦';
        var shareLink = domain + '/contact/filladdress?userName=' + userName;

        wx.onMenuShareAppMessage({
            title: shareTitle, // 分享标题
            desc: descContent, // 分享描述
            link: shareLink, // 分享链接
            imgUrl: imgUrl, // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });

        wx.onMenuShareAppMessage({
            title: shareTitle, // 分享标题
            desc: descContent, // 分享描述
            link: shareLink, // 分享链接
            imgUrl: imgUrl, // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });
//        WeixinJSBridge.on('menu:share:appmessage', function(argv){  // 好友
//            WeixinJSBridge.invoke('sendAppMessage',{
//                "appid": "",
//                "img_url": imgUrl,
//                "img_width": "640",
//                "img_height": "640",
//                "link": shareLink,
//                "desc": descContent,
//                "title": shareTitle
//            }, function(res) {
//            });
//        });
//        WeixinJSBridge.on('menu:share:timeline', function(argv){ // 朋友圈
//            alert("share to timeline");
//            WeixinJSBridge.invoke('shareTimeline',{
//                "img_url": imgUrl,
//                "img_width": "640",
//                "img_height": "640",
//                "link": shareLink,
//                "desc": descContent,
//                "title": shareTitle
//            }, function(res) {
//            });
//        });
    }

    // province and city selection
    var where = new Array(34);
    function comefrom(loca, locacity) {
        this.loca = loca; this.locacity = locacity;
    }
    where[0]  = new comefrom("省份", "城市");
    where[1]  = new comefrom("北京", "东城|西城|崇文|宣武|朝阳|丰台|石景山|海淀|门头沟|房山|通州|顺义|昌平|大兴|平谷|怀柔|密云|延庆");
    where[2]  = new comefrom("上海", "黄浦|卢湾|徐汇|长宁|静安|普陀|闸北|虹口|杨浦|闵行|宝山|嘉定|浦东|金山|松江|青浦|南汇|奉贤|崇明");
    where[3]  = new comefrom("天津", "和平|东丽|河东|西青|河西|津南|南开|北辰|河北|武清|红挢|塘沽|汉沽|大港|宁河|静海|宝坻|蓟县");
    where[4]  = new comefrom("重庆", "万州|涪陵|渝中|大渡口|江北|沙坪坝|九龙坡|南岸|北碚|万盛|双挢|渝北|巴南|黔江|长寿|綦江|潼南|铜梁|大足|荣昌|壁山|梁平|城口|丰都|垫江|武隆|忠县|开县|云阳|奉节|巫山|巫溪|石柱|秀山|酉阳|彭水|江津|合川|永川|南川");
    where[5]  = new comefrom("河北", "石家庄|邯郸|邢台|保定|张家口|承德|廊坊|唐山|秦皇岛|沧州|衡水");
    where[6]  = new comefrom("山西", "太原|大同|阳泉|长治|晋城|朔州|吕梁|忻州|晋中|临汾|运城");
    where[7]  = new comefrom("内蒙古","呼和浩特|包头|乌海|赤峰|呼伦贝尔盟|阿拉善盟|哲里木盟|兴安盟|乌兰察布盟|锡林郭勒盟|巴彦淖尔盟|伊克昭盟");
    where[8]  = new comefrom("辽宁", "沈阳|大连|鞍山|抚顺|本溪|丹东|锦州|营口|阜新|辽阳|盘锦|铁岭|朝阳|葫芦岛");
    where[9]  = new comefrom("吉林", "长春|吉林|四平|辽源|通化|白山|松原|白城|延边");
    where[10] = new comefrom("黑龙江","哈尔滨|齐齐哈尔|牡丹江|佳木斯|大庆|绥化|鹤岗|鸡西|黑河|双鸭山|伊春|七台河|大兴安岭");
    where[11] = new comefrom("江苏", "南京|镇江|苏州|南通|扬州|盐城|徐州|连云港|常州|无锡|宿迁|泰州|淮安");
    where[12] = new comefrom("浙江", "杭州|宁波|温州|嘉兴|湖州|绍兴|金华|衢州|舟山|台州|丽水");
    where[13] = new comefrom("安徽", "合肥|芜湖|蚌埠|马鞍山|淮北|铜陵|安庆|黄山|滁州|宿州|池州|淮南|巢湖|阜阳|六安|宣城|亳州");
    where[14] = new comefrom("福建", "福州|厦门|莆田|三明|泉州|漳州|南平|龙岩|宁德");
    where[15] = new comefrom("江西", "南昌市|景德镇|九江|鹰潭|萍乡|新馀|赣州|吉安|宜春|抚州|上饶");
    where[16] = new comefrom("山东", "济南|青岛|淄博|枣庄|东营|烟台|潍坊|济宁|泰安|威海|日照|莱芜|临沂|德州|聊城|滨州|菏泽");
    where[17] = new comefrom("河南", "郑州|开封|洛阳|平顶山|安阳|鹤壁|新乡|焦作|濮阳|许昌|漯河|三门峡|南阳|商丘|信阳|周口|驻马店|济源");
    where[18] = new comefrom("湖北", "武汉|宜昌|荆州|襄樊|黄石|荆门|黄冈|十堰|恩施|潜江|天门|仙桃|随州|咸宁|孝感|鄂州");
    where[19] = new comefrom("湖南", "长沙|常德|株洲|湘潭|衡阳|岳阳|邵阳|益阳|娄底|怀化|郴州|永州|湘西|张家界");
    where[20] = new comefrom("广东", "广州|深圳|珠海|汕头|东莞|中山|佛山|韶关|江门|湛江|茂名|肇庆|惠州|梅州|汕尾|河源|阳江|清远|潮州|揭阳|云浮");
    where[21] = new comefrom("广西", "南宁|柳州|桂林|梧州|北海|防城港|钦州|贵港|玉林|南宁地区|柳州地区|贺州|百色|河池");
    where[22] = new comefrom("海南", "海口|三亚");
    where[23] = new comefrom("四川", "成都|绵阳|德阳|自贡|攀枝花|广元|内江|乐山|南充|宜宾|广安|达川|雅安|眉山|甘孜|凉山|泸州");
    where[24] = new comefrom("贵州", "贵阳|六盘水|遵义|安顺|铜仁|黔西南|毕节|黔东南|黔南");
    where[25] = new comefrom("云南", "昆明|大理|曲靖|玉溪|昭通|楚雄|红河|文山|思茅|西双版纳|保山|德宏|丽江|怒江|迪庆|临沧");
    where[26] = new comefrom("西藏", "拉萨|日喀则|山南|林芝|昌都|阿里|那曲");
    where[27] = new comefrom("陕西", "西安|宝鸡|咸阳|铜川|渭南|延安|榆林|汉中|安康|商洛");
    where[28] = new comefrom("甘肃", "兰州|嘉峪关|金昌|白银|天水|酒泉|张掖|武威|定西|陇南|平凉|庆阳|临夏|甘南");
    where[29] = new comefrom("宁夏", "银川|石嘴山|吴忠|固原");
    where[30] = new comefrom("青海", "西宁|海东|海南|海北|黄南|玉树|果洛|海西");
    where[31] = new comefrom("新疆", "乌鲁木齐|石河子|克拉玛依|伊犁|巴音郭勒|昌吉|克孜勒苏柯尔克孜|博尔塔拉|吐鲁番|哈密|喀什|和田|阿克苏");
    where[32] = new comefrom("香港","");
    where[33] = new comefrom("澳门","");
    // where[34] = new comefrom("台湾","|台北|高雄|台中|台南|屏东|南投|云林|新竹|彰化|苗栗|嘉义|花莲|桃园|宜兰|基隆|台东|金门|马祖|澎湖");

    function select() {
        with (document.creator.province) {
            if (selectedIndex == -1)
                return;
            var loca2 = options[selectedIndex].value;
        }
        for (i = 0;i < where.length; i++) {
            if (where[i].loca == loca2) {
                loca3 = (where[i].locacity).split("|");
                for (j = 0; j < loca3.length; j++) {
                    with (document.creator.city) {
                        length = loca3.length; options[j].text = loca3[j]; options[j].value = loca3[j];
                    }
                }
                break;
            }
        }
    }

    function initSelect() {
        with (document.creator.province) {
            length = where.length;
            for (k = 0; k < where.length; k++) {
                options[k].text = where[k].loca;
                options[k].value = where[k].loca;
            }
            options[selectedIndex].text = where[0].loca; options[selectedIndex].value = where[0].loca;
        }
        with (document.creator.city) {
            loca3 = (where[0].locacity).split("|");
            length = loca3.length;
            for (l = 0; l < length; l++) {
                options[l].text = loca3[l]; options[l].value = loca3[l];
            }
            options[selectedIndex].text = loca3[0]; options[selectedIndex].value = loca3[0];
        }
    }

    var addressBook = new Contacts();
    $(function() {

//        wx.config({
//            debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
//            appId: $('#var-appid').val(), // 必填，公众号的唯一标识
//            timestamp: $('#var-timestamp').val(), // 必填，生成签名的时间戳
//            nonceStr: $('#var-noncestr').val(), // 必填，生成签名的随机串
//            signature: $('#var-sign').val(),// 必填，签名，见附录1
//            jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
//        });

        if (document.addEventListener) {
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        }
        addressBook.setUserName($("#var-user-name").val()).fetchContacts();
        initSelect();
        $("#province_select").change(function(){select();});

    });

})(jQuery);
