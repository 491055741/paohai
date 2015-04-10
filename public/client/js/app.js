
var Postcard = angular.module("Postcard", [
    "ngTouch",
    "ngRoute",
    "ngAnimate",
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
        .when("/editGreetings", {
            templateUrl: "templates/editGreetings.html",
            controller: "EditGreetingsController"
        })
        .when("/selectPostmark", {
            templateUrl: "templates/selectPostmark.html",
            controller: "SelectPostmarkController"
        })
        .when("/preview", {
            templateUrl: "templates/preview.html",
            controller: "PreviewController"
        })
        .when("/order", {
            templateUrl: "templates/order.html",
            controller: "OrderController"
        })
        .when("/friendsManager", {
            templateUrl: "templates/friendsManager.html",
            controller: "FriendsManagerController"
        })
        .when("/addContact", {
            templateUrl: "templates/addContact.html",
            controller: "AddContactController"
        })
        .when("/ordersManager", {
            templateUrl: "templates/ordersManager.html",
            controller: "OrdersManagerController"
        })
        .when("/continueOrder", {
            templateUrl: "templates/continueOrder.html",
            controller: "ContinueOrderController"
        })
        .when("/like", {
            templateUrl: "templates/like.html",
            controller: "LikeController"
        })
        .otherwise({
            templateUrl: "templates/404.html"
        });
    }
]);