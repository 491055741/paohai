postcardControllers.controller("DoneController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/order");
        };

        $rootScope.onHeaderRightButtonClick = function () {

        };

        $scope.orderDetail = function () {
            $location.url("/ordersManager");
        };

        $http.get("/postcard/complete/" + $rootScope.order.id + "?" + Util.getQueryStringFromObject({
            nonce: Util.getNonceStr()
        })).success(function (data) {
        }).error(function () {
        });

        $http.get("/postcard/getWeixinConfig?" + Util.getQueryStringFromObject({
        })).success(function (data) {
            Util.configWeixin(data.config);
        }).error(function () {
        });

        //var nickname = "";
        //$http.get("/contact/getUserInfo?" + Util.getQueryStringFromObject({
        //    userName: $rootScope.username
        //})).success(function (data) {
        //    nickname = data.data.nickname;
        //}).error(function () {
        //});

        wx.ready(function () {
            //var descContent = nickname.length > 0 ? '亲，您的好友[' + nickname + ']在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦' : '亲，您的好友在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦';
            var shareConfig = {
                title: "这张明信片是我自己定制的，快来看看吧。",
                desc: "您也想做这样的DIY明信片吗？赶紧关注服务号趣邮吧，寄出有温度的明信片，传递摸得着的祝福。", // 分享描述
                link: "http://" + $location.host() + ":" + $location.port() + "/client/index.html#/like?orderId=" + $rootScope.order.id,// 分享链接
                imgUrl: "http://quyou.quyoucard.com/images/small/logo.jpg",
                success: function () {
                },
                cancel: function () {
                }
            };
            wx.onMenuShareTimeline(shareConfig);
            wx.onMenuShareAppMessage(shareConfig);
        });

        Util.overlay.init("<img style='width: 100%' src='images/share-to-friends.png'/>");

        $scope.shareToFriend = function () {
            Util.overlay.show();
        };
    }
]);