
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
            .when("/editInfo", {
                templateUrl: "templates/editInfo.html",
                controller: "EditInfoController"
            })
            .when("/editContact", {
                templateUrl: "templates/editContact.html",
                controller: "EditContactController"
            })
            .when("/addressBook", {
                templateUrl: "templates/addressBook.html",
                controller: "AddressBookController"
            })
            .otherwise({
                templateUrl: "templates/404.html"
            });
    }
]);