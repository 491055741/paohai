postcardControllers.controller("EditContactController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.addressBook = function () {
            $location.path("/addressBook");
        };

        var targetContact = $rootScope.targetContact;
        if (targetContact) {
            $scope.name = targetContact.contactName;
            $scope.address = targetContact.address;
            $scope.mobile = targetContact.mobile;
            $scope.zipcode = targetContact.zipCode;
        }

        $scope.provinces = Provinces;
        $scope.cities = function () {
            if (!$scope.selectedProvince) {
                return [];
            }

            return JSON.parse($scope.selectedProvince).cities.split("|");
        };

        $scope.onOkButtonClick = function () {

            var name = $scope.name;
            if (!name) {
                alert("请填写联系人姓名");
                return;
            }

            if (!$scope.selectedProvince && !$scope.targetContact) {
                alert("请选择省份");
                return;
            }

            if ($scope.selectedProvince) {
                var province = JSON.parse($scope.selectedProvince).province;
            }

            var city = $scope.selectedCity;
            if (!city && !$scope.targetContact) {
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

            var detailAddress = province + city + address; // TODO: need to be test here.
            if (!$scope.targetContact) {
                detailAddress = address;
            }

            $http.post("/postcard/addcontact", {
                userName: $rootScope.username,
                contactName: name,
                address: detailAddress,
                zipCode: zipcode,
                mobile: mobile
            }).success(function (data) {
                $scope.data = data.data;

                if (!$rootScope.targetContact) {
                    $rootScope.targetContact = {};
                }

                $rootScope.targetContact.contactName = name;
                $rootScope.targetContact.address = detailAddress;
                $rootScope.targetContact.mobile = mobile;
                $rootScope.targetContact.zipCode = zipcode;

                $location.path("/editInfo");
            }).error(function (error) {
                alert(error);
            });
        };

        var myScroll = new IScroll('#iscrollWrapper', {
            click: true,
            scrollbars: true
        });
    }
]);