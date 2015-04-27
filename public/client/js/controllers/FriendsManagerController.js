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

        wx.onMenuShareTimeline({
            title: 'dfasfd', // 分享标题
            link: 'http://www.baiduc.com', // 分享链接
            imgUrl: '', // 分享图标
            success: function () {
                // 用户确认分享后执行的回调函数
                alert("success");
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
                alert("cancel");
            }
        });

        wx.checkJsApi({
            jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage'], // 需要检测的JS接口列表，所有JS接口列表见附录2,
            success: function(res) {
                alert(res);
                // 以键值对的形式返回，可用的api值true，不可用为false
                // 如：{"checkResult":{"chooseImage":true},"errMsg":"checkJsApi:ok"}
            }
        });

        wx.onMenuShareAppMessage({
            title: 'dfasf', // 分享标题
            desc: 'dfasdf', // 分享描述
            link: 'http://www.baiduc.com', // 分享链接
            imgUrl: 'http://quyou.quyoucard.com/images/small/logo.jpg', // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户确认分享后执行的回调函数
                alert("success");
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
                alert("cancel");
            }
        });
    }
]);