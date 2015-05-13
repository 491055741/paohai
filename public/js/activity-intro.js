(function($) {
    var domain = "http://" + window.location.host;
    var currentUrl = domain + window.location.pathname + window.location.search;
    var actId = 1;
    var partnerId = "";
    var userName = "";
    var accessToken = "";

    // Get jsApiSignPackage
    function init(inputActId, inputPartnerId) {
        var varObj = $("#global-var");
        partnerId = inputPartnerId;
        actId = inputActId;
        userName = varObj.data("username");
        accessToken = varObj.data("accesstoken");

        doWxConfig();
    }

    function doWxConfig() {
        $.get(
            domain + "/wechat/jsapisignpackage",
            {
                invokeUrl: currentUrl,
            },
            function success(data) {
                if (data.code != 0) {
                    alert("JS-SDK加载失败"); 
                    return;
                }
                var packageParam = data.data.jsApiSignPackage;
                wx.config({
                    // for debug
                    //debug: true,
                    appId: packageParam.appId,
                    timestamp:  packageParam.timestamp,
                    nonceStr:  packageParam.nonceStr,
                    signature: packageParam.signature,
                    jsApiList: ['chooseImage', 'uploadImage']
                });
            },
            "json"
        );
    }

    function chooseImage(callback) {
        wx.chooseImage({
            success: function(res) {
                var localIds = res.localIds;    // 图片的本地ID

                if (localIds.length > 1) {
                    alert("只能上传一张图片");
                    return;
                }

                $.each(localIds, function(index, localId) {
                    uploadImage(localId, callback);
                });
            }
        });
    }

    function uploadImage(imageId, callback) {
        wx.uploadImage({
            localId: imageId,
            isShowProgressTips: 1,
            success: function(res) {
                var serverId = res.serverId;    // 图片的服务器ID
                var postcardUrl = genPostcardUrl(serverId);
                callback({url: postcardUrl});
            }
        });
    }

    function genPostcardUrl(serverId) {
        var picUrl =  "http://file.api.weixin.qq.com/cgi-bin/media/get?"
            + "access_token=" + accessToken
            + "&media_id=" + serverId;

        //'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]. '/client/index.html#/?picurl='.$picUrl.'&username='.$fromUsername.'&nonce='.time();
        var url = domain + "/client/index.html#/?picurl="+ picUrl
            + "&actId=" + actId
            + "&nonce=" + (new Date().getTime());

        return url;

    }

    window.actIntro = {
        init: init,
        chooseImage: chooseImage
    };
})(jQuery);

