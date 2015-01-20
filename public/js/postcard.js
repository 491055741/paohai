(function($) {
    var domain = "http://" + window.location.host;
    var userOpenId = "";
    var popWindowInited = false;
    

    /*************** Util begin   ***************************/
    $.extend(HC, {
        log: function(data) {   //日志记录
            console.log(data);
        },
        showError: function(message, code) {
            if (code) {
                message += " code: " + code;
            }
            $(".popbox").find(".pop-title").text("出错啦").end()
                .find(".pop-message").text(message).end().show();
            if ( ! popWindowInited) {
                $(document).on("click", ".popbox .pop-close-button", function() {
                    $(".popbox").hide();
                });
                popWindowInited = true;
            }
        },
        showInfo: function(message, code) {
            if (code) {
                message += " code: " + code;
            }
            $(".popbox").find(".pop-title").text("提示").end()
                .find(".pop-message").text(message).end().show();
            if ( ! popWindowInited) {
                $(document).on("click", ".popbox .pop-close-button", function() {
                    $(".popbox").hide();
                });
                popWindowInited = true;
            }
        },
        loadingShow: function() {
            $(".loading-image").show();
        },
        loadingClose: function() {
            $(".loading-image").hide();
        },
        goToPage: function(url) {
            HC.loadingShow();
            window.location.href = url;        
        },
        getNonceStr: function() {
            return "" + new Date().getTime();
        },
        checkOrientation: function() {
            if (window.orientation == 90 || window.orientation == -90) {
                $(".orientation-tips").show();
            } else if (window.orientation === 0) {
//                if (window.innerWidth > window.innerHeight) {
//                    $(".orientation-tips").show();
//                } else {
                    $(".orientation-tips").hide();
//                }
            } else {
                $(".orientation-tips").hide();
            }
        },
        checkAddress: function(address) {
            // Check params
            if ( ! address.getName()) {
                return "请填写收件人姓名";
            }
            if (address.getName().length > 8) {
                return "您输入的收件人姓名太长啦, 请不要超过8个字符哦";
            }
            if ( ! address.getAddress()) {
                return "请填写收件人";
            }
            if (address.getAddress().length > 500) {
                return "您输入的地址太长啦，请不要超过500个字符哦";
            }
            var re= /^[0-9]{6}$/;
            if ( ! re.test(address.getZipcode())) {
                return "您输入的邮编格式不正确";
            }

            return "";
        },
    });

    /*************** Util begin   ***************************/

    /*************** Postcard define begin ******************/
    function PostcardImage() {
        var varCollection = {
            url: "",
            templateIndex: 1,
            offsetX: 0,
            offsetY: 0,
            isRotate: false          // 图片是否旋转(模板不同旋转不同)
        };
        $.extend(PostcardImage.prototype, {
            isComplete: function() {
                if ( ! varCollection.url) {
                    return false;
                }
                return true;
            },
            setVars: function(paramData) {
                $.extend(varCollection, paramData);
                return this;
            },
            getUrl: function() {
                return varCollection.url;
            },
            setUrl: function(url) {
                varCollection.url = url;
                return this;
            },
            getTemplateIndex: function() {
                return varCollection.templateIndex;
            },
            setTemplateIndex: function(templateIndex) {
                varCollection.templateIndex = templateIndex;
                return this;
            },
            getOffsetX: function() {
                return varCollection.offsetX;
            },
            setOffsetX: function(offsetX) {
                varCollection.offsetX = offsetX;
                return this;
            },
            getOffsetY: function() {
                return varCollection.offsetY;
            },
            setOffsetY: function(offsetY) {
                varCollection.offsetY = offsetY;
                return this;
            },
            getIsRotate: function() {
                return varCollection.isRotate;
            },
            setIsRotate: function(isRotate) {
                isRotate = isRotate ? true : false;
                varCollection.isRotate = isRotate;
                return this;
            }
        });
    }

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


    function Message() {
        var varCollection = {
            salutation: "",            // 收件人昵称
            content: "",
            signature: ""              // 发件人昵称
        };

        $.extend(Message.prototype, {
            isComplete: function() {
                if ( ! varCollection.salutation || ! varCollection.content || ! varCollection.signature) {
                    return false;
                }
                return true;
            },
            setVars: function(paramData) {
                $.extend(varCollection, paramData);
                return this;
            },
            getSalutation: function() {
                return varCollection.salutation;
            },
            setSalutation: function(salutation) {
                varCollection.salutation = salutation;
                return this;
            },
            getContent: function() {
                return varCollection.content;
            },
            setContent: function(content) {
                varCollection.content = content;
                return this;
            },
            getSignature: function() {
                return varCollection.signature;
            },
            setSignature: function(signature) {
                varCollection.signature = signature;
                return this;
            }
        });
    }

    function Postcard() {
        var varCollection = {
            image: new PostcardImage(),        // 明信片图片
            receiptAddress: new Address(),     // 收件人地址
            message: new Message(),            // 明信片祝福信息
            postmarkIndex: "",                  // 邮戳编号, ''表示默认不选中
        };

        $.extend(Postcard.prototype, {
            isComplete: function() {
                if ( 
                    ! varCollection.image.isComplete() ||
                    ! varCollection.receiptAddress.isComplete() ||
                    ! varCollection.message.isComplete()
                ) {
                    return false;
                }
                return true;
            },
            getUnCompleteInfo: function() {
                return {
                    image: varCollection.image.isComplete(),
                    receiptAddress: varCollection.receiptAddress.isComplete(),
                    message: varCollection.message.isComplete(),
                };
            },
            setVars: function(paramData) {
                $.extend(varCollection, paramData);
                return this;
            },
            getImage: function() {
                return varCollection.image;
            },
            getReceiptAddress: function() {
                return varCollection.receiptAddress;
            },
            getSenderAddress: function() {
                return varCollection.senderAddress;
            },
            getMessage: function() {
                return varCollection.message;
            },
            getPostmarkIndex: function() {
                return varCollection.postmarkIndex;
            },
            setPostmarkIndex: function(postmarkIndex) {
                varCollection.postmarkIndex = postmarkIndex;
                return this;
            },
        });
    }

    /*************** Postcard define end ******************/

    /*************** Contacts begin ***********************/

    function Contacts() {
        var addressBook = [];
        var chosedIndex = 0;                // 被选中的地址序号
        var isInited = false;

        $.extend(Contacts.prototype, {
            addContact: function(address) { // address为Address对象
                var url = domain + "/postcard/addcontact";
                var params = {
                    userName: userOpenId,
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
                    addressBook.push(address);
                }).fail(function(xmlhttprequest, err, e) {
                    if (err == "timeout") {
                        HC.showError("网速不给力，请稍候再试哦");
                    } else {
                        HC.showError("add contact failed !");
                    }
                });

                return this;
            },
            setChosenIndex: function(index) {
                // 超出数组大小，置为0
                index = index > addressBook.length ? 0 : index;
                chosenIndex = index;
                return this;
            },
            getChoseIndex: function() {
                return addressBook[chosedIndex];
            },
            fetchContacts: function() {
                addressBook = [];
                self = this;
                var url = domain + "/postcard/contacts?userName" + this.userName;
                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    timeout: 10000,
                }).done(function(data) {
                    $.each(data, function(i, v) {
                        var address = new Address();
                        address.setName(v.contactName)
                            .setAddress(v.address)
                            .setZipcode(v.zipCode);
                        addressBook.push(address);
                    });
                    self.showContacts();
                }).fail(function(xmlhttprequest, err, e) {
                    if (err == "timeout") {
                        HC.showError("网速不给力，请稍候再试哦");
                    } else {
                        HC.showError("add contact failed!");
                    }
                });

                return this;
            },
            showContacts: function() {
                if (isInited == false) {
                    this.fetchContacts();
                    isInited = true;
                }

                // TODO render contact
            },
        });
    }

    /*************** Contacts end *************************/


    /************ GeoLocation begin *******************/
    function GeoLocation() {
        this.CODE_TABLE = {
            OK: 0,
            PERMISSION_DENIED: 80001,       //用户未开启地理位置定位
            POSITION_UNAVAILABLE: 80002,    //地理位置信息不可得
            TIMEOUT: 80003,                 //获取地理位置超时
            UNKNOWN_ERROR: 80004,           
            BROSWER_NOT_SUPPORT: 80005,     //用户浏览器不支持地理位置地位
        };
        this.latlngExpireTime = 3600;       //位置过期时间 60 * 60

        this.coords = {
            latitude: 0,
            longitude: 0,
            lastUpdateTime: 0
        };

        this.code = this.CODE_TABLE.OK;
        
    };
    $.extend(GeoLocation.prototype, {
        init: function(latitude, longitude, lastUpdateTime) {
            this.coords.latitude = latitude;
            this.coords.longitude = longitude;
            this.coords.lastUpdateTime = lastUpdateTime;
        },
        getCoords: function() {
            return this.coords;
        },
        genLocation: function(successCallback, orderObj) {
            var self = this;
            this.code = this.CODE_TABLE.OK;

            var currTime = Date.parse(new Date()) / 1000;
            if (parseInt(this.coords.lastUpdateTime) + parseInt(this.latlngExpireTime) > parseInt(currTime)) {
                HC.showInfo("您已经获取定位戳，请到\"第三步\"预览页面查看");
            }

            if ( ! navigator.geolocation) {
                HC.showInfo("您的设备不支持地理位置定位");
            }
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    self.init(
                        position.coords.latitude,
                        position.coords.longitude,
                        currTime
                    );
                    successCallback.apply(orderObj);
                },
            this.processLatlngError);

        },
        processLatlngError: function(error) {
            var msg = "";
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    msg = "亲，您还没有开启定位功能";
                    break;
                case error.POSITION_UNAVAILABLE:
                    msg = "亲，地理位置获取失败，请重试";
                    break;
                case error.TIMEOUT:
                    msg = "亲，网络不给力，请重试";
                    break;
                case error.UNKNOWN_ERROR:
                    msg = "亲，您所在的位置不支持定位";
                    break;
            }
            HC.showError(msg);
        },
        getCode: function() {
            return this.code;
        }
    });
    GeoLocation.h5GeoAvailable = function() {
        if (window.navigator.userAgent.indexOf('iPhone') != -1) {
            return true;
        }
        return false;
    };

    /************ GeoLocation end *******************/

    /*************** Order begin *****************/
    function Order() {
        this.orderId = null;
        this.postcard = new Postcard();
        this.geo = new GeoLocation();
        this.userName = userOpenId;                     // 用户的OpenId

        var orderIsInited = false;
    }
    $.extend(Order.prototype, {
        getOrderId: function() {
            return this.orderId;
        },
        setOrderId: function(orderId) {
            this.orderId = orderId;
            return this;
        },
        getUserName: function() {
            return this.userName;
        },
        setUserName: function(userName) {
            this.userName = userName;
            return this;
        },
        getPostcard: function() {
            return this.postcard;
        },
        placeOrder: function(actId) {
            var url = domain + "/postcard/placeorder?nonce=" + HC.getNonceStr();
            var params = {
                templateIndex: this.postcard.getImage().getTemplateIndex(),
                offsetX: this.postcard.getImage().getOffsetX(),
                offsetY: this.postcard.getImage().getOffsetY(),
                userName: this.userName,
                userPicUrl: this.postcard.getImage().getUrl(),
                actId: actId,
            };
            $.post(
                url, 
                params, 
                function success(data) {
                    if (data.code != "0") {
                        HC.showError("Place order failed!", data.code);
                    } else {
                        var url = domain + "/postcard/editpostcard/" 
                            + data.orderId + "?nonce=" + HC.getNonceStr();
                        HC.goToPage(url);                        
                    }
                },
                "json"
            );
        },
        updateOrder: function(params, successCallback) {
            var url = domain + "/postcard/updateOrder/" + this.orderId
                + "?nonce=" + HC.getNonceStr();
            $.ajax({
                url: url,
                type: "POST",
                data: params,
                dataType: "json",
                timeout: 10000,
            }).done(function(data) {
                successCallback();
            }).fail(function(xmlhttprequest, err, e) {
                if (err == "timeout") {
                    HC.showError("网速不给力，请稍后再试");
                } else {
                    HC.showError("update order failed!");
                }
            });
        },
        updateImageForOrder: function() {
            var self = this;
            var params = {
                templateIndex: this.postcard.getImage().getTemplateIndex(),
                offsetX: this.postcard.getImage().getOffsetX(),
                offsetY: this.postcard.getImage().getOffsetY(),
            };
            return this.updateOrder(params, function() {
                var url = domain + "/postcard/editpostcard/" 
                    + self.getOrderId() + "?nonce=" + HC.getNonceStr();
                HC.goToPage(url);                        
            });
        },
        updateOrderAfterEdit: function() {
            var self = this;
            var params = {
                recipient: this.postcard.getReceiptAddress().getName(),
                address: this.postcard.getReceiptAddress().getAddress(),
                zipcode: this.postcard.getReceiptAddress().getZipcode(),
                salutation: this.postcard.getMessage().getSalutation(),
                message: this.postcard.getMessage().getContent(),
                signature: this.postcard.getMessage().getSignature(),
                postmarkId: this.postcard.getPostmarkIndex(),
            };
            return this.updateOrder(params, function() {
                var url = domain + "/wxpay/preview?orderId=" 
                    + self.getOrderId() + "&nonce=" + HC.getNonceStr();
                HC.goToPage(url);                        
            });
        },
        requestVoice: function() {
            var self = this;
            var params = {
                recipient: this.postcard.getReceiptAddress().getName(),
                address: this.postcard.getReceiptAddress().getAddress(),
                zipcode: this.postcard.getReceiptAddress().getZipcode(),
                salutation: this.postcard.getMessage().getSalutation(),
                message: this.postcard.getMessage().getContent(),
                signature: this.postcard.getMessage().getSignature(),
                postmarkId: this.postcard.getPostmarkIndex(),
            };
            return this.updateOrder(params, function() {
                var url = domain + "/postcard/requestvoice/" + self.orderId
                    + "?nonce=" + HC.getNonceStr();
                $.get(
                    url,
                    function success(data) {
                        if (data.errcode != "0" && data.code != "0") {
                            HC.showError("Send voice request failed (" + data.errmsg + ")", data.errcode);
                        } else {
                            if (typeof WeixinJSBridge == "undefined") {
                                HC.showError("不支持方法'closeWindow'. 请在微信浏览器中运行");
                            } else {
                                WeixinJSBridge.call("closeWindow");
                            }
                        }
                    },
                    "json"
                );
            });
        },
                      /*
        getUserLnglat: function() {
            var url = domain + "/postcard/userlnglat/" + this.orderId;
            $.get(
                url,
                function success(data) {
                    if (data.code != "0") {
                        HC.showError(data.msg, data.code);
                    } else if(data.lnglat.length == 0) {
                        HC.showError("请您设置微信，允许我们获取您的地理位置");
                    } else {
                        HC.showInfo("您已经获取定位戳，请到\"第三步\"预览页面查看");
                    }
                },
                "json"
            );
        },
        */
        getUserLnglat: function() {
            var self = this;
            var url = domain + "/postcard/userlnglat/" + this.orderId;
            $.get(
                url,
                function success(data) {
                    if (data.code != "0") {
                        HC.showError(data.msg, data.code);
                        return;
                    } else if (data.lnglat.length == 0) {
                        if ( ! GeoLocation.h5GeoAvailable()) {
                            HC.showError("请您设置微信，允许我们获取您的地理位置");
                            return;
                        }
                    } else {
                        var lastUpdateTime = isNaN(data.lnglat.lastUpdateTime) ? 0 : parseInt(data.lnglat.lastUpdateTime);
                        if ( ! GeoLocation.h5GeoAvailable()) {
                            var currTime = Date.parse(new Date()) / 1000;
                            if (lastUpdateTime + 21600 < parseInt(currTime)) {
                                HC.showError("请您设置微信，允许我们获取您的地理位置");
                                return;
                            } else {
                                HC.showInfo("您已经获取定位戳，请到\"第三步\"预览页面查看");
                                return;
                            }
                        }
                        self.geo.init(
                            data.lnglat.latitude,
                            data.lnglat.longitude,
                            data.lnglat.lastUpdateTime
                        );
                    }
                    self.geo.genLocation(self.clientReportLnglat, self);
                },
                "json"
            );
        },
        clientReportLnglat: function() {
            var self = this;
            var url = domain + "/postcard/clientreportlnglat/" + this.orderId;
            var geoData = this.geo.getCoords();
            var params = {
                username: this.userName,
                latitude: geoData.latitude,
                longitude: geoData.longitude,
            };
            $.ajax({
                url: url,
                type: "POST",
                data: params,
                dataType: "json",
                timeout: 10000,
            }).done(function(data) {
                if (data.code != 0) {
                    HC.showError(data.msg, data.code);
                } else {
                    HC.showInfo("您已经获取定位戳，请到\"第三步\"预览页面查看");
                }
            }).fail(function(xmlhttprequest, err, e) {
                if (err == "timeout") {
                    HC.showError("网速不给力，请稍候再试");
                } else {
                    HC.showError("report position failed!");
                }
            });
        },
        saveContact: function() {
            var url = domain + "/postcard/addcontact";    
            var params = {
                userName: this.userName,
                contactName: this.postcard.getReceiptAddress().getName(),
                address: this.postcard.getReceiptAddress().getAddress(),
                zipCode: this.postcard.getReceiptAddress().getZipcode(),
            };
            $.ajax({
                url: url,
                type: "POST",
                data: params,
                dataType: "json",
                timeout: 10000,
            }).done(function(data) {
                HC.log("save receiptAddress success");
            }).fail(function(xmlhttprequest, err, e) {
                if (err == "timeout") {
                    HC.showError("网速不给力，请稍候再试");
                } else {
                    HC.showError("save contact failed!");
                }
            });
        },
        getContacts: function(callback) {
            var url = domain + "/postcard/contacts?userName=" + this.userName;
            $.ajax({
                url: url,
                type: "GET",
                data: "",
                dataType: "json",
                timeout: 10000,
            }).done(function(data) {
                callback(data);
            }).fail(function(xmlhttprequest, err, e) {
                if (err == "timeout") {
                    HC.showError("网速不给力，请稍候再试");
                } else {
                    HC.showError("get contacts failed!");
                }
            });
        },
        goToStepOne: function() {
            var url = domain + "/postcard?orderId=" + this.getOrderId();
            HC.goToPage(url);                        
        },
        goToStepTwo: function() {
            var url = domain + "/postcard/editpostcard/" 
                + this.getOrderId() + "?nonce=" + HC.getNonceStr();
            HC.goToPage(url);                        
        },
        goToPay: function() {
            var url = domain + "/wxpay/asyncmakepicture/" + this.orderId;
            $.post(
                url,
                function success(data) {
                    // TODO callpay
                }
            );
        },
        pay: function(callback) {
            var self = this;
            var url = domain + "/wxpay/paypara/" + this.orderId;
            $.get(
                url,
                function success(data) {
                    if (data.code != 0) {
                        HC.showError(data.errmsg, data.code);
                        return;
                    }
                    if (data.price == 0) {
                        var completePageUrl = domain + "/postcard/complete/" + self.orderId + "?nonce=" + HC.getNonceStr();
                        HC.goToPage(completePageUrl);
                        return;
                    }
                    callback(data.payPara);
                },
                "json"
            );
        },
    });
    /*************** Order end *******************/


    window.order = new Order();
    window.contacts = new Contacts();
})(jQuery);
