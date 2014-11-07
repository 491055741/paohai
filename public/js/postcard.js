(function($) {
    var domain = "http://" + window.location.host;
    var userOpenId = "";
    

    /*************** Util begin   ***************************/
    $.extend(HC, {
        log: function(data) {   //日志记录
            console.log(data);
        },
        showError: function(message, code) {
            if (code) {
                message += " code: " + code;
            }
            alert(message);
        },
        showInfo: function(message, code) {
            if (code) {
                message += " code: " + code;
            }
            alert(message);
        },
        goToPage: function(url) {
            window.location.href = url;        
        },
        getNonceStr: function() {
            return "" + new Date().getTime();
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
        var varCollection = {
            name: "",
            address: "",
            zipcode: ""
        };

        $.extend(Address.prototype, {
            isComplete: function() {
                if ( ! varCollection.name || ! varCollection.address || ! varCollection.zipcode) {
                    return false;
                }
                return true;
            },
            setVars: function(paramData) {
                $.extend(varCollection, paramData);
                return this;
            },
            getName: function() {
                return varCollection.name;
            },
            setName: function(name) {
                varCollection.name = name;
                return this;
            },
            getAddress: function() {
                return varCollection.address;
            },
            setAddress: function(address) {
                varCollection.address = address;
                return this;
            },
            getZipcode: function() {
                return varCollection.zipcode;
            },
            setZipcode: function(zipcode) {
                varCollection.zipcode = zipcode;
                return this;
            }
        });
    }

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
            postmarkIndex: 0,                  // 邮戳编号
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

    /*************** Order begin *****************/
    function Order() {
        this.orderId = null;
        this.postcard = new Postcard();
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
        placeOrder: function() {
            var url = domain + "/postcard/placeorder?nonce=" + HC.getNonceStr();
            var params = {
                templateIndex: this.postcard.getImage().getTemplateIndex(),
                offsetX: this.postcard.getImage().getOffsetX(),
                offsetY: this.postcard.getImage().getOffsetY(),
                userName: this.userName,
                userPicUrl: this.postcard.getImage().getUrl(),
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
                    HC.showError("网速不给力，请稍候再试");
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
    });
    /*************** Order end *******************/


    window.order = new Order();
    window.contacts = new Contacts();
})(jQuery);
