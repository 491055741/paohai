(function($) {
    orderId = $('#orderId').val();
    $(function() {
//        var postcardurl = "http://" + window.location.host + "/postcard/shareimage/" + orderId;
    })

    wx.ready(function() {

        var imgUrl = 'https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoib04c8tvEHviaFSFtLGJ5Ox1H9CibIfOiauH0UEiaso13g5zgJ5E8SozibwIibESViaXMQ5keYwQAZwHLylw/0';
        var shareTitle = '我在趣邮向您索要收件地址';
        var userName = $('#var-user-name').val();
        var nickName = $('#var-nick-name').val();
        var descContent = nickName.length > 0 ? '亲，您的好友['+nickName+']在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦' : '亲，您的好友在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦';
        var shareLink = domain + '/contact/filladdress?userName=' + userName;

        wx.onMenuShareAppMessage({
            title: shareTitle, // 分享标题
            desc: descContent, // 分享描述
            link: shareLink, // 分享链接
            imgUrl: imgUrl, // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });

        wx.onMenuShareAppMessage({
            title: shareTitle, // 分享标题
            desc: descContent, // 分享描述
            link: shareLink, // 分享链接
            imgUrl: imgUrl, // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });
    });
})(jQuery);
