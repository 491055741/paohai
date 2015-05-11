postcardControllers.controller("OrderController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<上一步";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/preview");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        var info = Util.getProvinceAndCityFromAddress($rootScope.targetContact.address);

        if (info.province) {
            $scope.province = info.province;
            $scope.city = info.city;
            $scope.address = $rootScope.targetContact.address.replace(new RegExp($scope.province, "g"), "");
            $scope.address = $scope.address.replace(new RegExp($scope.city, "g"), "");
        }


        $scope.selectedPrice = 2.99;

        $scope.selectPrice = function (price) {
            $scope.selectedPrice = price;
            setTotalPrice();
        };

        $scope.checkClass = function (price) {
            return $scope.selectedPrice === price ? "checked" : null;
        };


        var payParameters = null;
        function setTotalPrice() {
            $scope.totalPrice = $scope.selectedPrice;
            $http.get("/wxpay/paypara/" + $rootScope.order.id, {
                params: {
                    orderId: $rootScope.order.id,
                    selectedPrice: $scope.selectedPrice,
                    coupon: $scope.refundCode,
                    response_type: 'code',
                    scope: 'snsapi_base',
                    state: $rootScope.order.id,
                    code: $rootScope.code,
                    openId: $rootScope.openId || $rootScope.username,
                    connect_redirect: 1
                }
            }).success(function (data) {
                if (data.code != 0) {
                    alert(data.msg);
                    return;
                }

                $scope.totalPrice = data.data.price;
                payParameters = JSON.parse(data.data.payPara);
            }).error(function (error) {
                alert(JSON.stringify(error));
                alert("获取支付参数失败");
            });
        }

        setTotalPrice();

        $scope.checkCoupon = function () {
            setTotalPrice();
        };

        $scope.pay = function () {
            if ($scope.totalPrice == 0) {
                $location.path("/done");
            } else {
                alert(JSON.stringify(payParameters));
                WeixinJSBridge.invoke("getBrandWCPayRequest",
                    {
                        "appId": payParameters.appId,
                        "timeStamp": payParameters.timeStamp,
                        "nonceStr": payParameters.nonceStr,
                        "package": payParameters.package,
                        "signType": payParameters.signType,
                        "paySign": payParameters.paySign
                    },
                    function(res){
                    if (res.err_msg == 'get_brand_wcpay_request:ok') { // pay success
                        $location.path("/done");
                    } else if (res.err_msg != 'get_brand_wcpay_request:cancel') { // fail with other reason, exclude user cancel
                        alert(res.err_msg);
                    }
                });
            }
        };

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 300);
    }
]);