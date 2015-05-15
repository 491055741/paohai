postcardControllers.controller("ScanToPayController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        var qrcode = new QRCode(document.getElementById("qrcode"), {
            width : 150,//设置宽高
            height : 150
        });

        $http.get("/wxpay/payQRpara/", {
            params: {
            }
        }).success(function (data) {
            if (data.code != 0) {
                alert(data.msg);
                return;
            }

            $scope.price = (data.data.price / 100).toFixed(2);

            qrcode.makeCode(data.data.codeURL);
        }).error(function (error) {
            alert(JSON.stringify(error));
            alert("获取支付参数失败");
        });
    }
]);