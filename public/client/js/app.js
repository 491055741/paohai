
var Postcard = angular.module("Postcard", [
    "ngTouch",
    "ngRoute",
    "PostcardControllers"
]);

Postcard.config(["$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/", {
                templateUrl: "templates/selectTemplate.html",
                controller: "SelectTemplateController"
            })
            .otherwise({
                templateUrl: "templates/404.html"
            });
    }
]);