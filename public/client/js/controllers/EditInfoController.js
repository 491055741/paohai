postcardControllers.controller("EditInfoController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<选择边框";
        $rootScope.rightButtonText = "确认预览>";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/");
        };

        $rootScope.onHeaderRightButtonClick = function () {
            $location.path("/preview");
        };

        $scope.editContact = function () {
            $location.path("/editContact");
        };

        $scope.selectPostmark = function () {
            $location.path("/selectPostmark");
        };

        $scope.editGreetings = function () {
            $location.path("/editGreetings");
        };

        // TODO: 设置一个默认邮戳

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