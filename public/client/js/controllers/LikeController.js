postcardControllers.controller("LikeController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<信息填写";
        $rootScope.rightButtonText = "";

        $rootScope.onHeaderLeftButtonClick = function () {
            $rootScope.templateOrder = $scope.order;
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
        };

        var orderId = $routeParams.orderId;

        var picture = new Image();
        var targetTemplate = null;
        $http.get("/postcard/getOrder?" + Util.getQueryStringFromObject({
            orderId: orderId
        })).success(function (data) {
            $scope.order = data.data.order;
            $scope.order.front = Util.getFrontUrl($scope.order);
            picture.src = $scope.order.front;
            picture.onload = function () {
                $http.get("/postcard/getTemplates?" + Util.getQueryStringFromObject({
                    orderId: $scope.order.id,
                    actId: $scope.order.activityId
                })).success(function (data) {
                    //$scope.order.templateId = 17;
                    var templates = data.data.imgTemplates;
                    for (var i = 0, length = templates.length; i < length; i++) {
                        var template = templates[i];
                        if ($scope.order.templateId == template.id) {
                            targetTemplate = template;
                            break;
                        }
                    }

                    draw();
                });
            };
        }).error(function () {
        });

        $scope.isLike = false;
        $scope.likeActive = function () {
            return $scope.isLike ? "like-active" : "like-inactive";
        };

        $scope.isUnlike = false;
        $scope.unlikeActive = function () {
            return $scope.isUnlike ? "unlike-active" : "unlike-inactive";
        };

        $scope.onClickLikeButton = function () {
            if (!$scope.isLike) {
                $scope.isLike = true;
                $scope.order.like  = parseInt($scope.order.like) + 1;
                $http.post("/postcard/updateOrder/" + orderId + "?nonce=" + Util.getNonceStr(), {
                    like: true
                }).success(function (data) {
                }).error(function (error) {
                    alert(error);
                });
            }
        };

        $scope.onClickUnlikeButton = function () {
            if (!$scope.isUnlike) {
                $scope.isUnlike = true;
                $scope.order.unlike = parseInt($scope.order.unlike) + 1;
                $http.post("/postcard/updateOrder/" + orderId + "?nonce=" + Util.getNonceStr(), {
                    unlike: true
                }).success(function (data) {
                }).error(function (error) {
                    alert(error);
                });
            }
        };

        function draw() {
            var canvas = document.getElementById("pictureCanvas");
            canvas.width = 1181;
            canvas.height = 1748;

            var imgScale = 1.0;
            var pixelRatio = Util.getPixelRatio();
            if (targetTemplate.rotate == -90) {
                $scope.selectTemplateType = 1;
                imgScale = canvas.height / picture.width / pixelRatio;
            } else {
                $scope.selectTemplateType = 0;
                imgScale = canvas.width / picture.width / pixelRatio;
            }

            var context = canvas.getContext("2d");
            var imgX = 0;
            var imgY = 0;
            function drawImage(){
                context.clearRect(-500, -1000, 2000, 2000);
                context.drawImage(picture, 0, 0, picture.width, picture.height, imgX * pixelRatio, imgY * pixelRatio, picture.width * imgScale * pixelRatio, picture.height * imgScale * pixelRatio);
            }



            if ($scope.selectTemplateType === 0) {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.rotate(0);
                context.drawImage(picture, 0, 0, picture.width, picture.height, imgX * pixelRatio, imgY * pixelRatio, picture.width * imgScale * pixelRatio, picture.height * imgScale * pixelRatio);
            } else {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.rotate(Math.PI / 2);
                imgX = 0;
                imgY = - picture.height * imgScale;
                context.drawImage(picture, 0, 0, picture.width, picture.height, imgX * pixelRatio, imgY * pixelRatio, picture.width * imgScale * pixelRatio, picture.height * imgScale * pixelRatio);
            }

            drawImage();

            if (targetTemplate) {
                var templateCanvas = document.getElementById("templateCanvas");
                templateCanvas.width = 1181;
                templateCanvas.height = 1748;
                var context = templateCanvas.getContext("2d");
                var img = new Image();
                img.src = targetTemplate.url;
                img.onload = function () {
                    context.clearRect(0, 0, templateCanvas.width, templateCanvas.height);
                    context.drawImage(img, 0, 0, templateCanvas.width, templateCanvas.height);
                };
            }
        }

        function reLayout() {
            var height = parseInt($("#templateCanvas").css("height"));
            $("#templateCanvas").css("width", 1181 * height / 1748);
            $("#templateCanvas").css("left", parseInt($("#cardPreview").css("width")) / 2 - parseInt($("#templateCanvas").css("width")) / 2);
            $("#pictureCanvas").css("width", $("#templateCanvas").css("width"));
            $("#pictureCanvas").css("height", $("#templateCanvas").css("height"));
            $("#pictureCanvas").css("left", $("#templateCanvas").css("left"));
            //draw();
        }

        reLayout();

        window.onresize = reLayout;
    }
]);