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
            $rootScope.selectedTemplate = $scope.showTemplates[$scope.selectTemplateIndex];

            if ($rootScope.order && $rootScope.order.id) {
                $http.post("/postcard/updateOrder/" + $rootScope.order.id + "?nonce=" + Util.getNonceStr(), {
                    templateIndex: $scope.showTemplates[$scope.selectTemplateIndex].id
                }).success(function (data) {
                    if (data.code === 0) {
                        $location.path("/editInfo");
                    } else {
                        alert(data.msg);
                    }
                }).error(function (error) {
                    alert(error);
                });
            } else {
                $http.post("/postcard/placeorder?nonce=" + Util.getNonceStr(), {
                    templateIndex: $scope.showTemplates[$scope.selectTemplateIndex].id,
                    offsetX: 0, // TODO: offsetX
                    offsetY: 0,
                    userName: $rootScope.username,
                    userPicUrl: $rootScope.picurl,
                    actId: $rootScope.activityId,
                    partnerId: null // TODO: partnerId
                }).success(function (data) {
                    if (data.code === 0) {
                        if (!$rootScope.order) {
                            $rootScope.order = {};
                        }

                        $rootScope.order.id = data.orderId;
                        $location.path("/editInfo");
                    } else {
                        alert(data.msg);
                    }
                }).error(function (error) {
                    alert(error);
                });
            }
        };

        $rootScope.code = $routeParams.code;
        $rootScope.openId = $routeParams.openId;
        if (!$rootScope.code) {
            $http.get("/postcard/getOauthUrl", {
                params: {
                    url: $location.absUrl()
                }
            }).success(function (data) {
                if (data.code == 0) {
                    $window.location.href = data.oauthUrl;
                } else {
                    alert("获取授权url错误");
                }
            }).error(function (error) {
                alert(error);
            });

            return;
        }

        alert($rootScope.code);

        $scope.selectTemplateType = 0;
        $scope.selectTemplateIndex = 0;
        $scope.showTemplates = [];
        $scope.data = null;

        function windowToCanvas(canvas, x, y){
            var bbox = canvas.getBoundingClientRect();
            return {
                x:x - bbox.left,
                y:y - bbox.top
            };
        }

        function draw() {
            var canvas = document.getElementById("pictureCanvas");
            var context = canvas.getContext("2d");
            var imgX = 0;
            var imgY = 0;
            var imgScale = 0.5;
            function drawImage(){
                context.clearRect(-300, -300, canvas.width * 5, canvas.height * 5);
                context.drawImage(picture, 0, 0, picture.width, picture.height, imgX, imgY, picture.width * imgScale, picture.height * imgScale);
            }

            if ($scope.selectTemplateType === 0) {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.rotate(0);
                context.drawImage(picture, 0, 0, picture.width, picture.height, imgX, imgY, picture.width * imgScale, picture.height * imgScale);
            } else {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.rotate(Math.PI / 2);
                imgX = 0;
                imgY = - picture.height * imgScale;
                context.drawImage(picture, 0, 0, picture.width, picture.height, imgX, imgY, picture.width * imgScale, picture.height * imgScale);
            }

            var templateCanvas = document.getElementById("templateCanvas");

            var center = {
                x: 0,
                y: 0
            };
            templateCanvas.ontouchstart = function (event){
                var startImageX = imgX;
                var startImageY = imgY;

                var pos = windowToCanvas(templateCanvas, event.touches[0].clientX, event.touches[0].clientY);

                if (event.touches.length === 2) {
                    var pos2 = windowToCanvas(templateCanvas, event.touches[1].clientX, event.touches[1].clientY);
                    center.x = (pos.x + pos2.x) / 2;
                    center.y = (pos.y + pos2.y) / 2;
                }

                var distance = 0;
                templateCanvas.ontouchmove = function(event){

                    if (event.touches.length === 1) {
                        var pos1 = windowToCanvas(templateCanvas,event.touches[0].clientX, event.touches[0].clientY);
                        var x = pos1.x - pos.x;
                        var y = pos1.y - pos.y;
                        pos = pos1;
                        if ($scope.selectTemplateType === 0) {
                            imgX += x;
                            imgY += y;
                        } else {
                            imgX += y;
                            imgY -= x;
                        }
                    }

                    if (event.touches.length === 2) {
                        var x1 = event.touches[0].clientX;
                        var y1 = event.touches[0].clientY;

                        var x2 = event.touches[1].clientX;
                        var y2 = event.touches[1].clientY;

                        var deltaDistance = 0;
                        if (distance === 0) {
                            distance = Math.pow(Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2), 0.5);
                            return;
                        } else {
                            deltaDistance = Math.pow(Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2), 0.5) - distance;
                        }

                        if(deltaDistance > 0){
                            imgScale *= 1.01;
                            imgX = center.x - imgScale * (center.x - startImageX);
                            imgY = center.y - imgScale * (center.y - startImageY);
                        }else{
                            imgScale *= 0.99;
                            imgX = center.x - imgScale * (center.x - startImageX);
                            imgY = center.y - imgScale * (center.y - startImageY);
                        }
                    }

                    drawImage();
                    //$("#data").html(imgX + "<br/>" + imgY + "<br/>" + imgScale);
                };
            };
        }

        var picture = new Image();
        $http.get("/postcard/getTemplates?" + Util.getQueryStringFromObject({
            orderId: $rootScope.order && $rootScope.order.id,
            picurl: $routeParams.picurl,
            actId: $rootScope.activityId,
            partnerId: $rootScope.partnerId,
            username: $routeParams.username
        })).success(function (data) {
            $scope.data = data.data;
            $rootScope.username = $scope.data.username;
            $rootScope.activityId = $scope.data.actId;
            $rootScope.order = $scope.data.order;
            $rootScope.picurl = $scope.data.picurl;
            showTemplate();
            picture.src = $rootScope.picurl;
            picture.onload = function () {
                draw();
            };
        }).error(function () {
        });

        $http.get("/postcard/getWeixinConfig?" + Util.getQueryStringFromObject({
        })).success(function (data) {
            Util.configWeixin(data.config);
        }).error(function () {
        });

        function reLayout() {
            var height = parseInt($("#templateCanvas").css("height"));
            $("#templateCanvas").css("width", 1181 * height / 1748);
            $("#templateCanvas").css("left", parseInt($("#image-container").css("width")) / 2 - parseInt($("#templateCanvas").css("width")) / 2);

            $("#pictureCanvas").css("width", $("#templateCanvas").css("width"));
            $("#pictureCanvas").css("left", $("#templateCanvas").css("left"));

            $("#pictureCanvas").attr("width", parseInt($("#pictureCanvas").css("width")));
            $("#pictureCanvas").attr("height", parseInt($("#pictureCanvas").css("height")));

            draw();
        }

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

                reLayout();
            }

            if ($scope.selectTemplateType === 1) {
                for (var key in $scope.data.imgTemplates) {
                    var template = $scope.data.imgTemplates[key];
                    if (template.rotate === "-90") {
                        $scope.showTemplates.push(template);
                    }
                }

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

            var canvas = document.getElementById("templateCanvas");
            var context = canvas.getContext("2d");
            var img = new Image();
            img.src = $scope.showTemplates[$scope.selectTemplateIndex] && $scope.showTemplates[$scope.selectTemplateIndex].url;
            img.onload = function () {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.drawImage(img, 0, 0, canvas.width, canvas.height);
            };
        };
    }
]);