(function($) {
    var domain = "http://" + window.location.host;
    var currentUrl = domain + window.location.pathname + window.location.search;
    var actId = 1;
    var userName = "";
    var accessToken = "";

    // Get jsApiSignPackage
    function init(actId, userName) {
        actId = actId ? actId : 1;
        userName = userName ? userName : "";
        getAccessToken();
        doWxConfig();
    }


    function getAccessToken() {
        var url = domain + "/wechat/accessToken";
        $.get(
            url, 
            function success(data) {
                if (data.code != 0) {
                    alert("出错了，请退出当前页面重新进入");
                    return;
                }
                accessToken = data.data.accessToken;
            },
            "json"
        );
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
        var userName = "";

        var url = domain + "/postcard?"
            + "actId=" + encodeURIComponent(actId)
            + "&picurl=" + encodeURIComponent(picUrl)
            + "&username=" + encodeURIComponent(userName)
            + "&nonce=" + HC.getNonceStr();

        return url;

    }

    window.actIntro = {
        init: init,
        chooseImage: chooseImage,
        uploadImage: uploadImage
    };
})(jQuery)


