(function($) {
    var imgUrl = 'https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAo8lFWa5TprPficNhSricKbd1f9QQ4e95lktmlc8WVIz3RvPj4XeMwZXoV9Bhtd3uIhkkPVYvbwD8ibgA/0';//这里是分享的时候的那个图片
    var descContent = '亲，您的好友在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦';
    var shareTitle = '我在趣邮向您索要收件地址';
    var appid = 'wxbd6694a085209f4d';  //这里写开发者接口里的appid
    
    var userName = $('#var-user-name').val();
    var lineLink = 'http://' + window.location.host + '/contact/filladdress?userName=' + userName;//这个是分享的网址
    function shareFriend() {
        WeixinJSBridge.invoke('sendAppMessage',{
                                "appid": appid,
                                "img_url": imgUrl,
                                "img_width": "640",
                                "img_height": "640",
                                "link": lineLink,
                                "desc": descContent,
                                "title": shareTitle
                                }, function(res) {
                                _report('send_msg', res.err_msg);
                                })
    }
    function shareTimeline() {
        WeixinJSBridge.invoke('shareTimeline',{
                                "img_url": imgUrl,
                                "img_width": "640",
                                "img_height": "640",
                                "link": lineLink,
                                "desc": descContent,
                                "title": shareTitle
                                }, function(res) {
                                _report('timeline', res.err_msg);
                                });
    }

    function onBridgeReady() {

        WeixinJSBridge.on('menu:share:appmessage', function(argv){  // 好友
            shareFriend();
            });
        WeixinJSBridge.on('menu:share:timeline', function(argv){ // 朋友圈
            shareTimeline();
            });
    }

    if (typeof WeixinJSBridge == "undefined") {
        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
    } else {
        onBridgeReady();
    }



})(jQuery);