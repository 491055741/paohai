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

        qrcode.makeCode("weixin://wxpay/bizpayurl?sign=XXXXX&appid=XXXXX&mch_id=XXXXX&product_id= XXXXXX &time_stamp=XXXXXX&nonce_str=XXXXX");
        qrcode.makeCode("http://www.qq.com");

        $http.get("/wxpay/payQRpara/", {
            params: {
            }
        }).success(function (data) {
            if (data.code != 0) {
                alert(data.msg);
                return;
            }
        }).error(function (error) {
            alert(JSON.stringify(error));
            alert("获取支付参数失败");
        });
    }
]);