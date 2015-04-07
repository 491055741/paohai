postcardControllers.controller("EditContactController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $window.history.back();
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.addressBook = function () {
            $location.path("/addressBook");
        };

        $scope.onOkButtonClick = function () {
            $location.path("/editInfo");
        };

        var myScroll = new IScroll('#iscrollWrapper');
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