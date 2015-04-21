postcardControllers.controller("OrderController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<上一步";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/preview");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        var info = Util.getProvinceAndCityFromAddress($rootScope.targetContact.address);

        if (info.province) {
            $scope.province = info.province;
            $scope.city = info.city;
            $scope.address = $rootScope.targetContact.address.replace(new RegExp($scope.province, "g"), "");
            $scope.address = $scope.address.replace(new RegExp($scope.city, "g"), "");
        }

        $scope.selectedPrice = 2.99;

        $scope.selectPrice = function (price) {
            $scope.selectedPrice = price;
        };

        $scope.checkClass = function (price) {
            return $scope.selectedPrice === price ? "checked" : null;
        };

        $scope.totalPrice = function () {
            return $scope.selectedPrice;
        };

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 300);

        //
        //$http.get("/postcard/getTemplates?" + Util.getQueryStringFromObject({
        //    //orderId: 0,
        //    picurl: $routeParams.picurl,
        //    //actId: "",
        //    //partnerId: "",
        //    username: $routeParams.username
        //})).success(function (data) {
        //    $scope.data = data.data;
        //    showTemplate();
        //}).error(function () {
        //});
    }
]);