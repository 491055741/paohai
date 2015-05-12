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

            if ($scope.selectedIndex === 0) {
                wx.getLocation({
                    success: function (res) {
                        $http.post("/postcard/clientreportlnglat/" + $rootScope.order.id, {
                            username: $rootScope.username,
                            latitude: res.latitude,
                            longitude: res.longitude
                        }).success(function (data) {
                            alert("您已经获取定位戳，请到预览页面查看");
                        }).error(function (error) {
                            alert(error);
                        });
                    },
                    fail: function (res) {
                        alert("fail: " + JSON.stringify(res));
                    }
                });
            }
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