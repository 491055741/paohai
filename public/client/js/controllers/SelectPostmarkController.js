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
                        var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                        var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                        var speed = res.speed; // 速度，以米/每秒计
                        var accuracy = res.accuracy; // 位置精度

                        alert(JSON.stringify(res));
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