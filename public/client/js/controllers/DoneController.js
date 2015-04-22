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
            $location.path("/ordersManager");
        };

        $http.get("/postcard/complete/" + $rootScope.order.id + "?" + Util.getQueryStringFromObject({
            nonce: Util.getNonceStr()
        })).success(function (data) {
        }).error(function () {
        });

        $scope.shareToFriend = function () {
            // TODO: share to friend.
            wx.onMenuShareAppMessage({
                title: '标题', // 分享标题
                desc: '分享描述', // 分享描述
                link: 'http://www.baidu.com', // 分享链接
                imgUrl: '', // 分享图标
                type: '', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                    // 用户确认分享后执行的回调函数
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                }
            });
        };
    }
]);