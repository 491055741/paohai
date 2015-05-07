postcardControllers.controller("ContinueOrderController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $rootScope.username = null;
            $location.path("/ordersManager");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.onOkButtonClick = function () {
            $location.path("/editInfo");
        };

        $scope.url = Util.getFrontUrl($rootScope.templateOrder);
        $rootScope.picurl = $scope.url;

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