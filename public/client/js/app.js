;
var Postcard = angular.module("Postcard", [
    "ngTouch",
    "ngRoute",
    "ngAnimate",
    "PostcardControllers"
], ["$httpProvider", function ($httpProvider) {
    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    /**
     * The workhorse; converts an object to x-www-form-urlencoded serialization.
     * @param {Object} obj
     * @return {String}
     */
    var param = function(obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for(name in obj) {
            value = obj[name];

            if(value instanceof Array) {
                for(i=0; i<value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value instanceof Object) {
                for(subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function(data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
}]);

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
        .when("/addContact/:index", {
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
        .when("/requestAddress", {
            templateUrl: "templates/requestAddress.html",
            controller: "RequestAddressController"
        })
        .when("/showRequestAddress", {
            templateUrl: "templates/showRequestAddress.html",
            controller: "ShowRequestAddressController"
        })
        .when("/done", {
            templateUrl: "templates/done.html",
            controller: "DoneController"
        })
        .when("/scanToPay", {
            templateUrl: "templates/scanToPay.html",
            controller: "ScanToPayController"
        })

        .otherwise({
            templateUrl: "templates/404.html"
        });
    }
]);