var orderId = '';
var senderName = '';
var senderAddress = '';
var userName = '';

$(document).on("pageinit", "#addressPage", function() {

    output("addressPage init");
    
    orderId = $('#orderId').val();
    userName = $('#userName').val();
    
    $('#submitAddressButton').fastClick(function() {
        submitAddress();
    });

    $('#saveRecipientToAddressBookBtn').fastClick(function() {
        getValueFromInput();
        if (recipient == '' || address == '') {
            return false;
        }
        addContact(recipient, address, zipcode, function() {});
    });

    $('#saveSenderToAddressBookBtn').fastClick(function() {
        getValueFromInput();
        var senderName = $("#senderNameInput").val();
        var senderAddress = $("#senderAddressInput").val();
        if (senderName == '' || senderAddress == '') {
            return false;
        }
        addContact(senderName, senderAddress, '', function() {});
    });

    $('#selectRecipientFromAddressBookBtn').fastClick(function() {
        getContacts(function() {
            changePage("#contactsPage");
        }, 'recipient');
    });

    $('#selectSenderFromAddressBookBtn').fastClick(function() {
        getContacts(function() {
            changePage("#contactsPage");
        }, 'sender');
    });
});

function submitAddress() {

    getValueFromInput();

    if (recipient == "" || recipient == null) {
        $.mobile.showPageLoadingMsg("b", "请填写收信人姓名", true);
        setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        return false;
    }

    if (address == "" || address == null) {
        $.mobile.showPageLoadingMsg("b", "请填写地址", true);
        setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        return false;
    }

    if (zipcode == "" || zipcode == null) {
        $.mobile.showPageLoadingMsg("b", "请填写邮编", true);
        setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        return false;
    }

    if (zipcode.length != 6 || isNaN(zipcode)) {
        $.mobile.showPageLoadingMsg("b", "邮编不正确", true);
        setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
        return false;
    }

    // if (mobile.length == 0 || mobile == null) {
    //     $.mobile.showPageLoadingMsg("b", "请填写收信人手机号码", true);
    //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
    //     return false;
    // }

    // if (mobile.length != 11 || isNaN(mobile)) {
    //     $.mobile.showPageLoadingMsg("b", "手机号码不正确", true);
    //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
    //     return false;
    // }

    changePage("#senderPage");
}

$(document).on("pageinit", "#senderPage", function() {
    output("senderPage init");

    orderId = $('#orderId').val();
    // $("#editAddressButton").fastClick(function() {
    //     var time = new Date().getTime();
    //     var redirect_uri = encodeURIComponent("http://paohai.ikamobile.com/wxpay/addr");
    //     var url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx4a41ea3d983b4538&redirect_uri="+redirect_uri+"&response_type=code&scope=snsapi_base&state="+time+"#wechat_redirect";
    //     self.location = url;
    //     return;
    // });

    $("#gotoPayButton").fastClick(function() {
        gotoPayPage();
    });

    $("#previewButton").fastClick(function() {
        uploadOrder(function() {
            var url = "http://" + window.location.host + "/postcard/preview/" + orderId + "?nonce=" + getNonceStr();
            self.location = url;
        });
    });
});

function gotoPayPage() {

    getValueFromInput();

    // if (senderName == "" || senderName == null) {
    //     $.mobile.showPageLoadingMsg("b", "请填写发信人姓名", true);
    //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
    //     return false;
    // }

    // if (senderAddress == "" || senderAddress == null) {
    //     $.mobile.showPageLoadingMsg("b", "请填写发信人地址", true);
    //     setTimeout("$.mobile.hidePageLoadingMsg()", 1000);
    //     return false;
    // }

    uploadOrder(function() {
        var url = "http://" + window.location.host + "/wxpay/pay?orderId=" + orderId + "&nonce=" + getNonceStr();
        self.location = url;        
    });
}

function uploadOrder(callback) {

    getValueFromInput();

    var url = "http://" + window.location.host + "/postcard/updateorder/" + orderId + "?nonce=" + getNonceStr();
    var params = {
        recipient: recipient,
        address: address,
        zipcode: zipcode,
        // mobile: mobile,
        senderName: senderName,
        senderAddress: senderAddress,
    };
    output('url: ' + url);

    $.ajax({
        url: url,
        type: 'POST',
        data:params,
        dataType: 'json',
        timeout: 1000,
        error: function(){
            alert('update order failed!');
        },
        success: function(result) {
            callback();
        }
    });
}

function getValueFromInput()
{
    address    = $("#addressInput").val();
    zipcode    = $("#zipcodeInput").val();
    recipient  = $("#recipientInput").val();
    mobile     = $("#mobileInput").val();

    senderName    = $("#senderNameInput").val();
    senderAddress = $("#senderAddressInput").val();
}

function addContact(contactName, contactAddress, zipCode, callback) {
    var url = "http://" + window.location.host + "/postcard/addcontact";
    var params = {
        userName: userName,
        contactName: contactName,
        address: contactAddress,
        zipCode: zipCode,
    };
    output('url: ' + url);

    $.ajax({
        url: url,
        type: 'POST',
        data:params,
        dataType: 'json',
        timeout: 1000,
        error: function(){
            alert('add contact failed!');
        },
        success: function(result) {
            callback();
            $("#saveRecipientToAddressBookBtn:visible, #saveSenderToAddressBookBtn:visible")
                .find(".nextbtnicon")
                .attr({"src": "/images/small/checkboxon.png"});
        }
    });    
}

function getContacts(callback, type) {

    var url = "http://" + window.location.host + "/postcard/contacts?userName=" + userName;
    output('url: ' + url);

    $.ajax({
        url: url,
        type: 'GET',
        data: '',
        dataType: 'json',
        timeout: 1000,
        error: function(){
            alert('get contacts failed!');
        },
        success: function(data) {

            // $.each(data, function(index, obj) {
                
            // });
            var json = data;
            $("#contactsList").empty();
            $("<table id='contactsTable' ></table>").appendTo("#contactsList");
            for (var i = 0; i < json.length; i++) {
                var contactsItem = [
                    "<tr class='contactsItem'>",
                        "<td class='contactItemName'>" + json[i].contactName + "</td>",
                        "<td class='contactItemAddress'>" + json[i].address + "</td>",
                        "<td class='contactItemZipcode'>" + json[i].zipCode + "</td>",
                    "</tr>",
                ];
                var contactsItemStr = contactsItem.join("");
                $(contactsItemStr).appendTo("#contactsTable");
            }
            $("#contactsTable").find(".contactsItem").click(function() {
                var name = $(this).find(".contactItemName").text();
                var address = $(this).find(".contactItemAddress").text();
                var zipcode = $(this).find(".contactItemZipcode").text();
                if (type == 'recipient') {
                    $("#recipientInput").val(name);
                    $("#addressInput").val(address);
                    $("#zipcodeInput").val(zipcode);
                } else if (type == 'sender') {
                    $("#senderNameInput").val(name);
                    $("#senderAddressInput").val(address);
                }
            });
            callback();
        }
    });
}
