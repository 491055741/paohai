postcardControllers.controller("AddressBookController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editContact");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.onOkButtonClick = function () {
            $location.path("/editContact");
        };

        var contacts = [
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
        $scope.contacts = contacts;

        $scope.onClickLi = function (index) {
            $scope.selectedIndex = index;
        };

        $scope.selectedClass = function (index) {
            return $scope.selectedIndex === index ? "selected" : null;
        };

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 200);

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