postcardControllers.controller("EditInfoController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<选择边框";
        $rootScope.rightButtonText = "确认预览>";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.editContact = function () {
            $location.path("/editContact");
        };

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