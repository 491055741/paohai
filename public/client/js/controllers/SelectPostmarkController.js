postcardControllers.controller("SelectPostmarkController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.onOkButtonClick = function () {
            $location.path("/editInfo");
        };

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 300);


        $http.get("/postcard/getYouchuoList?" + Util.getQueryStringFromObject({
            activityId: 0
        })).success(function (data) {
            $scope.data = data.data;
        }).error(function () {
        });
    }
]);