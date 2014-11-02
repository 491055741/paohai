(function($) {
    function PostcardImage() {
        var varCollection = {
            url: "",
            template: "",
            offsetX: 0,
            offsetY: 0,
            isRotate: false          // 图片是否旋转(模板不同旋转不同)
        };

        $.extend(PostcardImage.prototype, {
            setVars: function(paramData) {
                $.extend(varCollection, paramData);
            },
            getUrl: function() {
                return varCollection.url;
            },
            setUrl: function(url) {
                varCollection.url = url;
                return this;
            },
            getTemplate: function() {
                return varCollection.template;
            },
            setTemplate: function(template) {
                varCollection.template = template;
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
            setVars: function(paramData) {
                $.extend(varCollection, paramData);
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
            receiptNickname: "",            // 收件人昵称
            content: "",
            senderNickname: ""              // 发件人昵称
        };

        $.extend(Message.prototype, {
            setVars: function(paramData) {
                $.extend(varCollection, paramData);
            },
            getReceiptNickname: function() {
                return varCollection.receiptNickname;
            },
            setTitle: function(receiptNickname) {
                varCollection.receiptNickname = receiptNickname;
                return this;
            },
            getContent: function() {
                return varCollection.content;
            },
            setContent: function(content) {
                varCollection.content = content;
                return this;
            },
            getSenderNickname: function() {
                return varCollection.senderNickname;
            },
            setSenderNickname: function(senderNickname) {
                varCollection.senderNickname = senderNickname;
                return this;
            }
        });
    }

    function Postcard() {
        var varCollection = {
            image: new PostcardImage(),        // 明信片图片
            receiptAddress: new Address(),     // 收件人地址
            senderAddress: new Address(),      // 发件人地址
            message: new Message,              // 明信片祝福信息
            postmark: ""                       // 邮戳
        };

        $.extend(Postcard.prototype, {
            setVars: function(paramData) {
                $.extend(varCollection, paramData);
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
            }
        });
    }


    window.Postcard = Postcard;
})(jQuery);
