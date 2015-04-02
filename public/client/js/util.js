var util = angular.module("Util", []);

util.value("Util", {
    getQueryStringFromObject: function (object) {
        var queryString = "";
        for (var key in object) {
            if (object.hasOwnProperty(key)) {
                queryString += key + "=" + object[key] + "&";
            }
        }

        if (queryString.length > 1) {
            queryString = queryString.slice(0, -1);
        }

        return queryString;
    }
});