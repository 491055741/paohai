postcardControllers.controller("EditGreetingsController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<取消";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        $scope.startVoice = function () {
            $("#startVoice").text("正在录制，请说出您的留言...");
        };

        $scope.endVoice = function () {
            $("#startVoice").text("按住重录语音");
            $("#startVoice").css("right", "75px");
            $("#playVoice").show();
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