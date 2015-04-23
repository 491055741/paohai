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
            $http.get("/postcard/setOpenId", {
                params: {
                    url: $location.absUrl()
                }
            }).success(function (data) {
            }).error(function (error) {
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
        };

        $scope.selectedClass = function (index) {
            return $scope.selectedIndex === index ? "selected" : null;
        };

        $scope.isSelected = function (index) {
            return ($scope.selectedIndex === index);
        };

        $scope.addContact = function () {
            $location.path("/addContact");
        };

        Util.overlay.init("<h2>dkfla;sksadf</h2>");

        $scope.showOverlay = function () {
            Util.overlay.show();
        };

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 200);
    }
]);