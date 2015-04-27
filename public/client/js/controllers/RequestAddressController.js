postcardControllers.controller("RequestAddressController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
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

        $scope.provinces = Provinces;

        $scope.cities = function () {
            if (!$scope.selectedProvince) {
                return [];
            }

            for (var i = 0, length = Provinces.length; i < length; i++) {
                if (Provinces[i].province === $scope.selectedProvince) {
                    return Provinces[i].cities.split("|");
                }
            }

            return [];
        };

        $scope.onOkButtonClick = function () {
            var name = $scope.name;
            if (!name) {
                alert("请填写联系人姓名");
                return;
            }

            var province = $scope.selectedProvince;
            if (!$scope.selectedProvince) {
                alert("请选择省份");
                return;
            }

            var city = $scope.selectedCity;
            if (!city) {
                alert("请选择城市");
                return;
            }

            var address = $scope.address;
            if (!address) {
                alert("请填写详细地址");
                return;
            }

            var mobile = $scope.mobile;
            if (!mobile) {
                alert("请填写手机号");
                return;
            }

            var zipcode = $scope.zipcode;
            if (!zipcode) {
                alert("请填写邮编");
                return;
            }

            var detailAddress = province + city + address;

            if (confirm("您确定信息准确无误，要提交吗？")) {
                $http.post("/postcard/addcontact", {
                    userName: $rootScope.username,
                    contactName: name,
                    address: detailAddress,
                    zipCode: zipcode,
                    mobile: mobile
                }).success(function (data) {
                    $location.path("/friendsManager");
                }).error(function (error) {
                    alert(error);
                });
            }
        };

        var myScroll = new IScroll('#iscrollWrapper', {
            click: true,
            scrollbars: true
        });
    }
]);