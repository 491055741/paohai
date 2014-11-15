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
                        //HC.showError(data.msg, data.code);
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
                    addrObj.find(".addr-title").text(address.getName()).end()
                        .find(".addr-name").text(address.getName()).end()
                        .find(".addr-addr").text(address.getAddress()).end()
                        .find(".addr-post").text(address.getZipcode()).end()
                        .appendTo("div.address-book");
                });

                if ( ! isInited) {
                    // Event
                    $(document).on("click", ".list-wrap-hc .addr-title", function() {
                        $(".list-wrap-hc .list-info-ab").removeClass("on").find("ul").hide();
                        $(this).parents(".list-info-ab").addClass("on").find("ul").show();
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
                            .find(".to_who").val(addressObj.find(".addr-name").text()).attr("disabled", true).end()
                            .find(".postcode").val(addressObj.find(".addr-post").text()).end()
                            .find(".to_address").val(addressObj.find(".addr-addr").text()).end()
                            .show();
                    });
                    $(document).on("click", "#pop-cancel", function() {
                        $("#pop-address").hide();
                    });
                    $(document).on("click", "#pop-confirm", function() {
                        var address = new Address();
                        address.setVars({
                            "name": $("#pop-address .to_who").val(),
                            "address": $("#pop-address .to_address").val(),
                            "zipcode": $("#pop-address .postcode").val(),
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
                            .find(".to_who").val("").attr("disabled", false).end()
                            .find(".postcode").val("").end()
                            .find(".to_address").val("").end()
                            .show();
                    });

                    isInited = true;
                }
            },
        });
    }

    var addressBook = new Contacts();
    /*************** Contacts end *************************/


    $(function() {
        addressBook.setUserName($("#var-user-name").val())
            .fetchContacts();
    });
})(jQuery);
