postcardControllers.controller("FriendsManagerController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
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

        Util.overlay.init("<h2>dkfla;sksadf</h2>");

        $scope.showOverlay = function () {
            Util.overlay.show();
        };
    }
]);