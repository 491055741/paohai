postcardControllers.controller("OrdersManagerController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            // TODO: back to weixin page.
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        var orders = [
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            },
            {
                name: "dafsfd",
                address: "dfasdfs",
                postcode: "2334234"
            }
        ];

        $scope.selectedIndex = null;
        $scope.orders = orders;

        $scope.onClickLi = function (index) {
            $scope.selectedIndex = index;
        };

        $scope.selectedClass = function (index) {
            return $scope.selectedIndex === index ? "selected" : null;
        };

        $scope.isSelected = function (index) {
            return ($scope.selectedIndex === index);
        };

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 200);

        $scope.continueOrder = function (index) {
        };

        Util.overlay.init("<h2>dkfla;sksadf</h2>");
        $scope.shareOrder = function (index) {
            Util.overlay.show();
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