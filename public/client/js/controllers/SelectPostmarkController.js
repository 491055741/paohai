postcardControllers.controller("SelectPostmarkController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.selectedIndex = null;

        $scope.getClass = function ($index) {
            return {
                selected: $scope.selectedIndex === $index
            };
        };

        $scope.onClickLi = function ($index) {
            $scope.selectedIndex = $index;
        };

        $scope.onOkButtonClick = function () {
            $rootScope.youchuo = $scope.youchuoList[$scope.selectedIndex];
            $location.path("/editInfo");
        };

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 300);

        $http.get("/postcard/getYouchuoList?" + Util.getQueryStringFromObject({
            activityId: $rootScope.activityId
        })).success(function (data) {
            $scope.youchuoList = data.data;
        }).error(function () {
        });
    }
]);