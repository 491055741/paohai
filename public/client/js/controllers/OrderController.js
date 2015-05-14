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

        function setTotalPrice() {
            $scope.totalPrice = $scope.selectedPrice;
            $scope.priceTitle = "共计消费";

            $http.get("/wxpay/paypara/" + $rootScope.order.id, {
                params: {
                    orderId: $rootScope.order.id,
                    selectedPrice: $scope.selectedPrice,
                    coupon: $scope.refundCode,
                    openId: $rootScope.username,
                    truePay: 0
                }
            }).success(function (data) {
                if (data.code != 0) {
                    alert(data.msg);
                    return;
                }

                $scope.totalPrice = (data.data.price / 100).toFixed(2);
                if ($scope.totalPrice == "0.00") {
                    $scope.priceTitle = "活动价";
                }
            }).error(function (error) {
                alert(JSON.stringify(error));
                alert("获取支付参数失败");
            });
        }

        setTotalPrice();

        $scope.checkCoupon = function () {
            setTotalPrice();
        };

        $("#sureToPay").on("touchstart", function () {
            $http.get("/wxpay/paypara/" + $rootScope.order.id, {
                params: {
                    orderId: $rootScope.order.id,
                    selectedPrice: $scope.selectedPrice,
                    coupon: $scope.refundCode,
                    openId: $rootScope.username,
                    truePay: 1
                }
            }).success(function (data) {
                if (data.code != 0) {
                    alert(data.msg);
                    return;
                }

                if (data.data.price > 0) {
                    var payParameters = JSON.parse(data.data.payPara);
                    wx.chooseWXPay({
                        timestamp: payParameters.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                        nonceStr: payParameters.nonceStr, // 支付签名随机串，不长于 32 位
                        package: payParameters.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                        signType: payParameters.signType, // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                        paySign: payParameters.paySign, // 支付签名
                        success: function (res) {
                            $window.location.href = "http://" + $location.host() + ":" + $location.port() + "/client/index.html#/done?orderId=" + $rootScope.order.id;
                        },
                        fail: function (res) {
                            alert(JSON.stringify(res));
                        }
                    });
                } else {
                    $window.location.href = "http://" + $location.host() + ":" + $location.port() + "/client/index.html#/done?orderId=" + $rootScope.order.id;
                }
            }).error(function (error) {
                alert(JSON.stringify(error));
                alert("获取支付参数失败");
            });
        });

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 300);
    }
]);