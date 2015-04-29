postcardControllers.controller("LikeController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<信息填写";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        var orderId = $routeParams.orderId;

        $http.get("/postcard/getOrder?" + Util.getQueryStringFromObject({
            orderId: orderId
        })).success(function (data) {
            $scope.order = data.data.order;
            var year = $scope.order.orderDate.slice(0, 4);
            var month = $scope.order.orderDate.slice(5, 7);
            var day = $scope.order.orderDate.slice(8, 10);
            $scope.order.front = "/postcards/" + year + month + day + "/" + $scope.order.id + "_front.jpg";
        }).error(function () {
        });

        $scope.isLike = false;
        $scope.likeActive = function () {
            return $scope.isLike ? "like-active" : "like-inactive";
        };

        $scope.isUnlike = false;
        $scope.unlikeActive = function () {
            return $scope.isUnlike ? "unlike-active" : "unlike-inactive";
        };

        $scope.onClickLikeButton = function () {
            if (!$scope.isLike) {
                $scope.isLike = true;
                $scope.order.like  = parseInt($scope.order.like) + 1;
                $http.post("/postcard/updateOrder/" + orderId + "?nonce=" + Util.getNonceStr(), {
                    like: true
                }).success(function (data) {
                }).error(function (error) {
                    alert(error);
                });
            }
        };

        $scope.onClickUnlikeButton = function () {
            if (!$scope.isUnlike) {
                $scope.isUnlike = true;
                $scope.order.unlike = parseInt($scope.order.unlike) + 1;
                $http.post("/postcard/updateOrder/" + orderId + "?nonce=" + Util.getNonceStr(), {
                    unlike: true
                }).success(function (data) {
                }).error(function (error) {
                    alert(error);
                });
            }
        };
    }
]);