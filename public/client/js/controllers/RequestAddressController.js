postcardControllers.controller("RequestAddressController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            // TODO: go to the weixin page.
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.onOkButtonClick = function () {
            $location.path("/editInfo");
        };

        var myScroll = new IScroll('#iscrollWrapper', {
            click: true,
            scrollbars: true
        });
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