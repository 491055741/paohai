postcardControllers.controller("FriendsManagerController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            if (WeixinJSBridge) {
                WeixinJSBridge.call("closeWindow");
            }
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $rootScope.username = $routeParams.username;
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

        $http.get("/contact/listcontacts", {
            params: {
                userName: $rootScope.username
            }
        }).success(function (data) {
            $scope.contacts = data.data;
            setTimeout(function () {
                var myScroll = new IScroll('#iscrollWrapper', {
                    click: true,
                    scrollbars: true
                });
            }, 200);
        }).error(function (error) {
            alert(error);
        });

        $scope.selectedIndex = null;

        $scope.onClickLi = function (index) {
            $scope.selectedIndex = index;
            $(window).trigger('resize');
        };

        $scope.selectedClass = function (index) {
            return $scope.selectedIndex === index ? "selected" : null;
        };

        $scope.selectedArrow = function (index) {
            return $scope.selectedIndex === index ? "arrow-down" : "arrow-up";
        };

        $scope.isSelected = function (index) {
            return ($scope.selectedIndex === index);
        };

        $scope.editContact = function ($index) {
            if ($index === undefined) {
                $location.path("/addContact/false");
            } else {
                $rootScope.editContact = $scope.contacts[$index];
                $location.path("/addContact/" + $index);
            }
        };

        $scope.deleteContact = function ($index) {
            var contact = $scope.contacts[$index];
            var ok = confirm("您确定要删除  " + contact.contactName + "  吗?");

            if (!ok) {
                return;
            }

            $http.post("/contact/delete", {
                userName: $rootScope.username,
                contactName: contact.contactName
            }).success(function (data) {
                if (data.code == 0) {
                    $scope.contacts.splice($index, 1);
                } else {
                    alert(data.msg);
                }
            }).error(function (error) {
                alert(error);
            });
        };

        Util.overlay.init("<img style='width: 100%' src='images/share-to-friends.png'/>");

        $scope.showOverlay = function () {
            Util.overlay.show();
        };

        $http.get("/postcard/getWeixinConfig?" + Util.getQueryStringFromObject({
        })).success(function (data) {
            Util.configWeixin(data.config);
        }).error(function () {
        });

        var nickname = "";
        $http.get("/contact/getUserInfo?" + Util.getQueryStringFromObject({
            userName: $rootScope.username
        })).success(function (data) {
            nickname = data.data.nickname;
        }).error(function () {
        });

        wx.ready(function () {
            var descContent = nickname.length > 0 ? '亲，您的好友[' + nickname + ']在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦' : '亲，您的好友在趣邮向您索要收件地址，快去填写吧，可能有惊喜礼物收哦';
            var shareConfig = {
                title: "我在趣邮向您索要收件地址",
                desc: descContent, // 分享描述
                link: "http://" + $location.host() + ":" + $location.port() + "/client/index.html#/requestAddress?username=" + $rootScope.username,// 分享链接
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