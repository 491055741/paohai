postcardControllers.controller("AddressBookController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editContact");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.onOkButtonClick = function () {
            if ($scope.selectedIndex === null) {
                alert("请选择一个联系人");
                return;
            }

            $rootScope.targetContact = $scope.contacts[$scope.selectedIndex];
            console.log($rootScope.targetContact);
            $location.path("/editContact");
        };

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
    }
]);