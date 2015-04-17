postcardControllers.controller("EditGreetingsController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.voiceId = null;

        var timer = null;
        $scope.startVoice = function () {
            var total = 59;
            $("#startVoice").text("(" + total + ")正在录制，请说出您的留言...");
            wx.startRecord();
            timer = setInterval(function () {
                total--;
                if (total >= 0) {
                    $("#startVoice").text("(" + total + ")正在录制，请说出您的留言...");
                } else {
                    clearInterval(timer);
                    $("#startVoice").trigger("touchend");
                }
            }, 1000);

            wx.onVoiceRecordEnd({
                complete: end
            });
        };

        function end(res) {
            $("#startVoice").text("按住重录语音");
            $("#startVoice").css("right", "75px");
            $("#playVoice").show();
            $scope.voiceId = res.localId;
            clearInterval(timer);

            wx.uploadVoice({
                localId: $scope.voiceId,
                isShowProgressTips: 1, // 默认为1，显示进度提示
                success: function (res) {
                    $http.get('/postcard/downloadvoicemedia/'+ $rootScope.order.id +'?mediaId=' + res.serverId).success(function (data) {
                        $http.post("/postcard/updateOrder/" + $rootScope.order.id + "?nonce=" + Util.getNonceStr(), {
                            voiceMediaId: res.serverId
                        }).success(function (data) {
                        }).error(function (error) {
                            alert(error);
                        });
                    }).error(function (error) {
                        alert(error);
                    });
                }
            });
        }

        $scope.endVoice = function () {
            wx.stopRecord({
                success: end
            });
        };

        $("#startVoice").on("touchstart", function () {
            $scope.startVoice();
        });

        $("#startVoice").on("touchend", function () {
            $scope.endVoice();
        });

        var status = 'normal';
        $scope.playVoice = function () {
            if (status === 'normal') {
                wx.playVoice({
                    localId: $scope.voiceId
                });
                status = 'playing';
                $("#playVoice").addClass("pause");
            } else if (status === 'pause') {
                wx.playVoice({
                    localId: $scope.voiceId
                });
                status = 'playing';
                $("#playVoice").addClass("pause");
            } else if (status === 'playing') {
                wx.pauseVoice({
                    localId: $scope.voiceId
                });
                status = 'pause';
                $("#playVoice").removeClass("pause");
            }
        };

        wx.onVoicePlayEnd({
            success: function (res) {
                $("#playVoice").removeClass("pause");
                status = 'normal';
            }
        });

        $scope.onOkButtonClick = function () {
            $rootScope.message = $scope.message;
            $location.path("/editInfo");
        };
    }
]);