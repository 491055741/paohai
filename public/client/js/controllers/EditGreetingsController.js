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
            var total = 5;
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