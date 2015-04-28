postcardControllers.controller("ShowRequestAddressController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
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

        $http.get("/contact/getContact", {
            params: {
                userName: $rootScope.username,
                contactName: $routeParams.contactname
            }
        }).success(function (data) {
            var contact = data.data;
            $scope.name = contact.contactName;

            var info = Util.getProvinceAndCityFromAddress(contact.address);

            if (info.province) {
                $scope.selectedProvince = info.province;
                $scope.selectedCity = info.city;
                $scope.address = $scope.address.replace(new RegExp($scope.selectedProvince, "g"), "");
                $scope.address = $scope.address.replace(new RegExp($scope.selectedCity, "g"), "");
            }

            $scope.mobile = contact.mobile;
            $scope.zipcode = contact.zipCode;
        }).error(function (error) {
            alert(error);
        });

        $scope.onOkButtonClick = function () {
            $location.path("/friendsManager?&username=" + $rootScope.username);
        };
    }
]);