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

    $('#selectRecipientFromAddressBookBtn').fastClick(function() {
        getContacts(function() {
            changePage("#contactsPage");
        }, 'recipient');
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
    userName = $('#userName').val();
    // $("#editAddressButton").fastClick(function() {
    //     var time = new Date().getTime();
    //     var redirect_uri = encodeURIComponent("http://paohai.ikamobile.com/wxpay/addr");
    //     var url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx4a41ea3d983b4538&redirect_uri="+redirect_uri+"&response_type=code&scope=snsapi_base&state="+time+"#wechat_redirect";
    //     self.location = url;
    //     return;
    // });

    $('#saveSenderToAddressBookBtn').fastClick(function() {
        getValueFromInput();
        var senderName = $("#senderNameInput").val();
        var senderAddress = $("#senderAddressInput").val();
        if (senderName == '' || senderAddress == '') {
            return false;
        }
        addContact(senderName, senderAddress, '', function() {});
    });

    $('#selectSenderFromAddressBookBtn').fastClick(function() {
        getContacts(function() {
            changePage("#contactsPage");
        }, 'sender');
    });

    $("#gotoPayButton").fastClick(function() {
        gotoPay();
    });

    $("#previewButton").fastClick(function() {
        uploadOrder(function() {
            var url = "http://" + window.location.host + "/wxpay/preview?orderId=" + orderId + "&nonce=" + getNonceStr();
            self.location = url;
        });
    });
});

function gotoPay() {

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
        // var url = "http://" + window.location.host + "/wxpay/pay?orderId=" + orderId + "&nonce=" + getNonceStr();
        // self.location = url;
        callPay();
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
        senderAddress: senderAddress
    };
    output('url: ' + url);

    $.ajax({
        url: url,
        type: 'POST',
        data:params,
        dataType: 'json',
        timeout: 10000,
        error: function(xmlhttprequest, err, e){
            if (err == 'timeout') {
                alert("网速不给力，请稍后再试哦");
            } else {
                alert('update order failed!');
            }
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
        zipCode: zipCode
    };
    output('url: ' + url);

    $.ajax({
        url: url,
        type: 'POST',
        data:params,
        dataType: 'json',
        timeout: 10000,
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
        timeout: 10000,
        error: function () {
            alert('get contacts failed!');
        },
        success: function (data) {
            var arr = data;
            $("#contactsList").empty();

            /*[huangchun 2014-9-22]*/
            var list_dom = "";
                //声明用于包裹列表的容器list_wrap
            //var list_title = $("<h2 id='list_title_hc'>地址簿</h2>");
            var list_wrap = $("<div class='list-wrap-hc' id='list'></div>");
                //循环输出联系方式list，并追加到列表包裹容器中
            for (var i = 0,len = arr.length; i < len ; i++) {
                var list_str = [ "<div class='list-ul-hc'><ul>" ,
                    "<li><span class='addr-title'>姓名：</span>" + "<span class='addr-content name-hc'>" + arr[i].contactName +"</span>"+"</li>"+
                    "<li><span class='addr-title'>地址：</span>" + "<span class='addr-content addr-hc'>" + arr[i].address +"</span>"+"</li>"+
                    "<li><span class='addr-title'>邮编：</span>" + "<span class='addr-content post-hc'>" + arr[i].zipCode +"</span>"+"</li>",
                    "</ul><img src='/images/small/single_unselected.png' class='sel-btn-hc'></img></div>"
                ];
                list_dom = list_str.join("");
                list_wrap.append(list_dom);
            }
                //声明电话簿底部菜单
            var menu_btn ="<div class='menu-btn-hc' id='m_btn'>" + "<a href='#' id='close_hc'>关闭</a><a href='#' id='sure_hc'>确定</a>" + "</div>";
                //将列表和底部菜单追加到页面
            //$("#contactsList").append(list_title);
            $("#contactsList").append(list_wrap);
            $("#contactsList").append(menu_btn);
                //当用户选中某个联系人时，获取相应的数据
            var select_btn = $("#list .list-ul-hc");
            var tmp_name = '';
            var tmp_address = '';
            var tmp_zipcode = '';

            $(".list-ul-hc").on("click", function () {
                select_btn.each(function () {
                    $(this).find(".sel-btn-hc").attr({'src': "/images/small/single_unselected.png"});
                });
                $(this).find(".sel-btn-hc").attr({"src": "/images/small/single_selected.png"});

                tmp_name = $(this).find(".name-hc").text();
                tmp_address = $(this).find(".addr-hc").text();
                tmp_zipcode = $(this).find(".post-hc").text();

            });

            $("#close_hc").on("click", function() {
                window.history.go(-1);
            });

            $("#sure_hc").on("click", function() {
                if (type == 'recipient') {
                    $("#recipientInput").val(tmp_name);
                    $("#addressInput").val(tmp_address);
                    $("#zipcodeInput").val(tmp_zipcode);
                } else if (type == 'sender') {
                    $("#senderNameInput").val(tmp_name);
                    $("#senderAddressInput").val(tmp_address);
                }
                
                window.history.go(-1);
            });
            /*end*/
            callback();
        }
    });
}

