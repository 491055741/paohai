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

        $scope.startVoice = function () {
            $("#startVoice").text("正在录制，请说出您的留言...");
            wx.startRecord();

            wx.onVoiceRecordEnd({
                complete: function (res) {
                    $scope.voiceId = res.localId;
                }
            });
        };

        $scope.endVoice = function () {
            $("#startVoice").text("按住重录语音");
            $("#startVoice").css("right", "75px");
            $("#playVoice").show();

            wx.stopRecord({
                success: function (res) {
                    $scope.voiceId = res.localId;
                }
            });
        };

        $("#startVoice").on("touchstart", function () {
            $scope.startVoice();
        });

        $("#startVoice").on("touchend", function () {
            $scope.endVoice();
        });

        $scope.playVoice = function () {
            wx.playVoice({
                localId: $scope.voiceId
            });
        };

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