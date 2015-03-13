(function($) {

    var domain = "http://" + window.location.host;
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
                    mobile : address.getMobile()
                };
                $.ajax({
                    url: url,
                    type: "POST",
                    data: params,
                    dataType: "json",
                    timeout: 10000
                }).done(function(data) {
                    WeixinJSBridge.call('closeWindow');
                }).fail(function(xmlhttprequest, err, e) {
                    if (err == "timeout") {
                        HC.showError("网速不给力，请稍候再试哦");
                    } else {
                        HC.showError("添加联系人失败!");
                    }
                });

                return this;
            }
        })
    }

    var addressBook = new Contacts();

    /*************** Contacts end *************************/

    $(function() {
        LocalitySelection.initSelect();
        $("#province_select").change(function(){LocalitySelection.select();});

        addressBook.setUserName($("#var-user-name").val());

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

            addressBook.saveContact(address);
            WeixinJSBridge.call('closeWindow');
        });

//        $(document).on("click", "#pop-cancel", function() {
//
//        });
    });

})(jQuery);
