postcardControllers.controller("SelectTemplateController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
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

        $rootScope.username = $routeParams.username;
        $rootScope.activityId = $routeParams.actId;
        if (!$rootScope.username) {
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

        var canvas = document.getElementById("pictureCanvas");
        canvas.width = 2000;
        canvas.height = 2000;

        function draw() {
            var context = canvas.getContext("2d");
            var imgX = 0;
            var imgY = 0;
            var imgScale = 0.5;
            var pixelRatio = Util.getPixelRatio();
            function drawImage(){
                context.clearRect(-500, -1000, 2000, 2000);

                if ($scope.selectTemplateType === 0) {
                    if (imgX <= 0 && picture.width * imgScale < canvas.width / pixelRatio) {
                        imgX = 0;
                    }

                    if (imgX <= 0 && imgX + picture.width * imgScale < canvas.width / pixelRatio && picture.width * imgScale >= canvas.width / pixelRatio) {
                        imgX = canvas.width / pixelRatio - (imgX + picture.width * imgScale) + imgX;
                    }

                    if (imgX > 0 && imgX + picture.width * imgScale > canvas.width / pixelRatio && picture.width * imgScale < canvas.width / pixelRatio) {
                        imgX = canvas.width / pixelRatio - picture.width * imgScale;
                    }

                    if (imgX > 0 && imgX + picture.width * imgScale > canvas.width / pixelRatio && picture.width * imgScale >= canvas.width / pixelRatio) {
                        imgX = 0;
                    }

                    if (imgY <= 0 && picture.height * imgScale < canvas.height / pixelRatio) {
                        imgY = 0;
                    }

                    if (imgY <= 0 && imgY + picture.height * imgScale < canvas.height / pixelRatio && picture.height * imgScale >= canvas.height / pixelRatio) {
                        imgY = canvas.height / pixelRatio - (imgY + picture.height * imgScale) + imgY;
                    }

                    if (imgY > 0 && imgY + picture.height * imgScale > canvas.height / pixelRatio && picture.height * imgScale < canvas.height / pixelRatio) {
                        imgY = canvas.height / pixelRatio - picture.height * imgScale;
                    }

                    if (imgY > 0 && imgY + picture.height * imgScale > canvas.height / pixelRatio && picture.height * imgScale >= canvas.height / pixelRatio) {
                        imgY = 0;
                    }
                } else {

                    var offset = - picture.height * imgScale;
                    if (imgY >= offset && picture.height * imgScale < canvas.width / pixelRatio) {
                        imgY = offset;
                    }

                    if (imgY > offset && imgY >= -canvas.width / pixelRatio && picture.height * imgScale >= canvas.width / pixelRatio) {
                        imgY = - canvas.width / pixelRatio;
                    }

                    if (imgY <= offset && imgY < - canvas.width / pixelRatio && picture.height * imgScale < canvas.width / pixelRatio) {
                        imgY =  - canvas.width / pixelRatio ;
                    }

                    if (imgY <= offset && picture.height * imgScale >= canvas.width / pixelRatio) {
                        imgY = offset;
                    }

                    if (imgX <= 0 && picture.width * imgScale < canvas.height / pixelRatio) {
                        imgX = 0;
                    }

                    if (imgX <= 0 && imgX + picture.width * imgScale < canvas.height / pixelRatio && picture.width * imgScale >= canvas.height / pixelRatio) {
                        imgX = canvas.height / pixelRatio - (imgX + picture.width * imgScale) + imgX;
                    }

                    if (imgX > 0 && imgX + picture.width * imgScale > canvas.height / pixelRatio && picture.width * imgScale < canvas.height / pixelRatio) {
                        imgX = canvas.height / pixelRatio - picture.width * imgScale;
                    }

                    if (imgX > 0 && imgX + picture.width * imgScale > canvas.height / pixelRatio && picture.width * imgScale >= canvas.height / pixelRatio) {
                        imgX = 0;
                    }

                }


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
                            imgScale *= 1.02;
                        }else{
                            imgScale *= 0.98;
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
            $rootScope.picurl = decodeURIComponent($scope.data.picurl);

            if ($routeParams.mediaId) {
                $rootScope.picurl += "&media_id=" + $routeParams.mediaId;
            }

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

            $("#pictureCanvas").attr("width", parseInt($("#pictureCanvas").css("width")) * 2);
            $("#pictureCanvas").attr("height", parseInt($("#pictureCanvas").css("height")) * 2);

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


        var templateCanvas = document.getElementById("templateCanvas");
        templateCanvas.width = 2000;
        templateCanvas.height = 2000;
        $scope.selectedTemplate = function (index) {
            $scope.selectTemplateIndex = index;
            var context = templateCanvas.getContext("2d");
            var img = new Image();
            img.src = $scope.showTemplates[$scope.selectTemplateIndex] && $scope.showTemplates[$scope.selectTemplateIndex].url;
            img.onload = function () {
                context.clearRect(0, 0, templateCanvas.width, templateCanvas.height);
                context.drawImage(img, 0, 0, templateCanvas.width, templateCanvas.height);
            };
        };


        Util.overlay.init("<img style='width: 100%' src='images/tips.png'/><div id='closeIcon' style='width: 50px; height: 50px; position: absolute; top: 0; right: 0;'></div>", {transparent: true});
        Util.overlay.show();

        $("#closeIcon").on("click", function () {
            Util.overlay.hide();
        });
    }
]);