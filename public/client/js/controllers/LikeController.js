postcardControllers.controller("LikeController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<信息填写";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $rootScope.templateOrder = $scope.order;
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        var orderId = $routeParams.orderId;

        $http.get("/postcard/getOrder?" + Util.getQueryStringFromObject({
            orderId: orderId
        })).success(function (data) {
            $scope.order = data.data.order;
            $scope.order.front = Util.getFrontUrl($scope.order);
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