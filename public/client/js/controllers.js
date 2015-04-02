var postcardControllers = angular.module("PostcardControllers", ["Util"]);

postcardControllers.controller("SelectTemplateController", ["$rootScope", "$scope", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<更换图片";
        $rootScope.rightButtonText = "信息填写>";

        $rootScope.onHeaderLeftButtonClick = function () {
            console.log("left");
            //TODO: WeixinJSBridge.call('closeWindow');
        };

        $rootScope.onHeaderRightButtonClick = function () {
            console.log("right");
        };

        $scope.selectTemplateType = 0;
        $scope.selectTemplateIndex = 0;
        $scope.showTemplates = [];
        $scope.data = null;

        $http.get("/postcard/getTemplates?" + Util.getQueryStringFromObject({
            //orderId: 0,
            picurl: $routeParams.picurl,
            //actId: "",
            //partnerId: "",
            username: $routeParams.username
        })).success(function (data) {
            $scope.data = data.data;
            showTemplate();
        }).error(function () {
        });

        function showTemplate() {
            $scope.showTemplates = [];
            if ($scope.selectTemplateType === 0) {
                for (var key in $scope.data.imgTemplates) {
                    var template = $scope.data.imgTemplates[key];
                    if (template.rotate === "0") {
                        $scope.showTemplates.push(template);
                    }
                }
            }

            if ($scope.selectTemplateType === 1) {
                for (var key in $scope.data.imgTemplates) {
                    var template = $scope.data.imgTemplates[key];
                    if (template.rotate === "-90") {
                        $scope.showTemplates.push(template);
                    }
                }
            }
        }

        $scope.reshowTemplates = function (templateType) {
            $scope.selectTemplateType = templateType;
            showTemplate();
        };

        $scope.getClass = function (templateType) {
            if ($scope.selectTemplateType === templateType) {
                return "active-01";
            }

            return null;
        };

        $scope.showUpTriangle = function (templateType) {
            return $scope.selectTemplateType === templateType;
        };

        $scope.selectedTemplate = function (index) {
            $scope.selectTemplateIndex = index;
        };

        $scope.coverTemplate = function () {
            return $scope.showTemplates[$scope.selectTemplateIndex].url;
        }
    }
]);