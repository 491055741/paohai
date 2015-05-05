postcardControllers.controller("EditInfoController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<选择边框";
        $rootScope.rightButtonText = "确认预览>";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/");
        };

        $rootScope.targetContact.contactName = name;
        $rootScope.targetContact.address = detailAddress;
        $rootScope.targetContact.mobile = mobile;
        $rootScope.targetContact.zipCode = zipcode;

        $rootScope.onHeaderRightButtonClick = function () {
            $http.post("/postcard/updateOrder/" + $rootScope.order.id + "?nonce=" + Util.getNonceStr(), {
                zipcode: $rootScope.targetContact.zipCode,
                message: $rootScope.message,
                $address: $rootScope.targetContact.address,
                recipient: $rootScope.targetContact.contactName,
                mobile: $rootScope.targetContact.mobile
            }).success(function (data) {
                if (data.code === 0) {
                } else {
                    alert(data.msg);
                }
            }).error(function (error) {
                alert(error);
            });

            $location.path("/preview");
        };

        $scope.editContact = function () {
            $location.path("/editContact");
        };

        $scope.selectPostmark = function () {
            $location.path("/selectPostmark");
        };

        $scope.editGreetings = function () {
            $location.path("/editGreetings");
        };

        if (!$rootScope.order) {
            $http.post("/postcard/placeorder?nonce=" + Util.getNonceStr(), {
                templateIndex: $rootScope.templateOrder.templatedId,
                offsetX: $rootScope.templateOrder.offsetX,
                offsetY: $rootScope.templateOrder.offsetY,
                userName: $rootScope.username,
                userPicUrl: $rootScope.templateOrder.picUrl,
                actId: $rootScope.templateOrder.activityId,
                partnerId: null // TODO: partnerId
            }).success(function (data) {
                if (data.code === 0) {
                    if (!$rootScope.order) {
                        $rootScope.order = {};
                    }

                    $rootScope.order.id = data.orderId;
                    $location.path("/editInfo");
                } else {
                    alert(data.msg);
                }
            }).error(function (error) {
                alert(error);
            });
        }

        // TODO: 设置一个默认邮戳

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