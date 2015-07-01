postcardControllers.controller("PreviewController", ["$rootScope", "$scope", "$window", "$location", "$http", "$routeParams", "Util",
    function($rootScope, $scope, $window, $location, $http, $routeParams, Util) {
        $rootScope.leftButtonText = "<信息填写";
        $rootScope.rightButtonText = "确认支付>";

        $rootScope.onHeaderLeftButtonClick = function () {
            $location.path("/editInfo");
        };

        $rootScope.onHeaderRightButtonClick = function () {
            $location.path("/order");
        };

        setTimeout(function () {
            var myScroll = new IScroll('#iscrollWrapper', {
                click: true,
                scrollbars: true
            });
        }, 300);

        var picture = new Image();
        picture.src =  $rootScope.picurl;
        picture.onload = function () {
            draw();
        };

        var targetTemplate = $rootScope.selectedTemplate;
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
        }

        reLayout();
        //draw();

        //window.onresize = reLayout;
    }
]);