postcardControllers.controller("OrdersManagerController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util", "$filter",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util, $filter) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            if (WeixinJSBridge) {
                WeixinJSBridge.call("closeWindow");
            }
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $rootScope.username = null;
        if (!$rootScope.username) {
            $http.get("/postcard/getOauthUrl", {
                params: {
                    url: $location.absUrl()
                }
            }).success(function (data) {
                if (data.code == 0) {
                    $window.location.href = data.oauthUrl;
                } else {
                    alert("获取授权url错误");
                }
            }).error(function (error) {
                alert(error);
            });

            return;
        }

        $http.get("/postcard/getOrders", {
            params: {
                userName: $rootScope.username
            }
        }).success(function (data) {
            var orders = data.data.orders;
            $scope.orders = {};
            $scope.dateGroup = [];

            for (var i = 0, length = orders.length; i < length; i++) {
                var order = orders[i];
                var year = order.orderDate.slice(0, 4);
                var month = order.orderDate.slice(5, 7);
                var day = order.orderDate.slice(8, 10);

                var groupDate = year + "年" + month + "月";
                order.date = year + "." + month + "." + day;

                switch (order.status) {
                    case "99":
                        order.statusText = "已取消";
                        break;
                    case "100":
                        order.statusText = "待支付";
                        break;
                    case "101":
                        order.statusText = "已支付";
                        break;
                    case "102":
                        order.statusText = "已打印";
                        break;
                    case "103":
                        order.statusText = "已发货";
                        break;
                    default :
                        order.statusText = "未知";
                }

                if (!$scope.orders[groupDate]) {
                    $scope.orders[groupDate] = [];
                    $scope.dateGroup.push(groupDate);

                    if ($scope.dateGroup.length === 1) {
                        $scope.selectedDate = groupDate;
                        $scope.selectedIndex = 0;
                    }
                }

                order.front = Util.getFrontUrl(order);
                $scope.orders[groupDate].push(order);
            }

            setTimeout(function () {
                var myScroll = new IScroll('#iscrollWrapper', {
                    click: true,
                    scrollbars: true
                });
            }, 1000);
        }).error(function (error) {
        });

        $scope.selectedIndex = null;
        $scope.selectedDate = null;

        $scope.onClickDate = function (date) {
            $scope.selectedDate = date;
        };

        $scope.activeDate = function (date) {
            return $scope.selectedDate === date ? "active-tab" : null;
        };

        $scope.onClickLi = function (index) {
            $(window).trigger('resize');
            $scope.selectedIndex = index;
        };

        $scope.selectedArrow = function (index) {
            return $scope.selectedIndex === index ? "arrow-down" : "arrow-up";
        };

        $scope.selectedClass = function (index) {
            return $scope.selectedIndex === index ? "selected" : null;
        };

        $scope.isSelected = function (index) {
            return ($scope.selectedIndex === index);
        };

        $scope.continueOrder = function (index) {
            $rootScope.templateOrder = $scope.orders[$scope.selectedDate][$scope.selectedIndex];
            $location.path("/continueOrder");
        };

        Util.overlay.init("<img style='width: 100%' src='images/share-to-friends.png'/>");

        $scope.shareOrder = function (index) {
            Util.overlay.show();
        };

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
            var selectedOrder = $scope.orders[$scope.selectedDate][$scope.selectedIndex];
            //var descContent = nickname.length > 0 ? '亲，您的好友[' + nickname + ']在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦' : '亲，您的好友在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦';
            var shareConfig = {
                title: "这张明信片是我自己定制的，快来看看吧。",
                desc: "您也想做这样的DIY明信片吗？赶紧关注服务号趣邮吧，寄出有温度的明信片，传递摸得着的祝福。", // 分享描述
                link: "http://" + $location.host() + ":" + $location.port() + "/client/index.html#/like?orderId=" + selectedOrder.id,// 分享链接
                imgUrl: "http://quyou.quyoucard.com/images/small/logo.jpg",
                success: function () {
                },
                cancel: function () {
                }
            };
            wx.onMenuShareTimeline(shareConfig);
            wx.onMenuShareAppMessage(shareConfig);
        });
    }
]);