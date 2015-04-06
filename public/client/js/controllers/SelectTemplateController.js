postcardControllers.controller("SelectTemplateController", ["$rootScope", "$scope", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<更换图片";
        $rootScope.rightButtonText = "信息填写>";

        $rootScope.onHeaderLeftButtonClick = function () {
            if (WeixinJSBridge) {
                WeixinJSBridge.call("closeWindow");
            }
        };

        $rootScope.onHeaderRightButtonClick = function () {
            $location.path("/editInfo");
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

        var originWidth = null;
        var originHeight = null;

        function reLayout() {

            if (!originWidth) {
                originWidth = parseInt($("#picture").css("width"));
            }

            if (!originHeight) {
                originHeight = parseInt($("#picture").css("height"));
            }

            var height = parseInt($("#coverTemplate").css("height"));
            $("#coverTemplate").css("width", 1181 * height / 1748);
            $("#coverTemplate").css("left", parseInt($("#image-container").css("width")) / 2 - parseInt($("#coverTemplate").css("width")) / 2);



            if ($scope.selectTemplateType == 0 && originHeight && originWidth) {
                $("#picture").css("height", originHeight);
                $("#picture").css("width", originWidth);
                $("#picture").css("left", parseInt($("#image-container").css("width")) / 2 - originWidth / 2);
            }

            if ($scope.selectTemplateType == 1) {
                $("#picture").css("width", height);
                //$("#picture").css("height", originHeight * height / originWidth); // TODO: need to be fixed here.
                $("#picture").css("left", parseInt($("#image-container").css("width")) / 2 - height / 2);
            }
        }


        $("#picture").on("load", function () {
            reLayout();
        });

        $("#picture").on("click", function () {
            $("#picture").css("transform", "scale(2)");
            $("#picture").css("-webkit-transform", "scale(2)");
            console.log("3232");
        });

        window.onresize = reLayout;

        function showTemplate() {

            $scope.showTemplates = [];
            if ($scope.selectTemplateType === 0) {
                for (var key in $scope.data.imgTemplates) {
                    var template = $scope.data.imgTemplates[key];
                    if (template.rotate === "0") {
                        $scope.showTemplates.push(template);
                    }
                }

                $("#picture").removeClass("rotate-90");
                reLayout();
            }

            if ($scope.selectTemplateType === 1) {
                for (var key in $scope.data.imgTemplates) {
                    var template = $scope.data.imgTemplates[key];
                    if (template.rotate === "-90") {
                        $scope.showTemplates.push(template);
                    }
                }

                $("#picture").addClass("rotate-90");
                reLayout();
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
            return $scope.showTemplates[$scope.selectTemplateIndex] && $scope.showTemplates[$scope.selectTemplateIndex].thumbUrl;
        }
    }
]);