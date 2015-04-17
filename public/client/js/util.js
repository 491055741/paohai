var util = angular.module("Util", []);

util.value("Util", {
    getQueryStringFromObject: function (object) {
        var queryString = "";
        for (var key in object) {
            if (object.hasOwnProperty(key)) {
                if (object[key] !== undefined) {
                    queryString += key + "=" + object[key] + "&";
                }
            }
        }

        if (queryString.length > 1) {
            queryString = queryString.slice(0, -1);
        }

        return queryString;
    },

    overlay: {
        init: function (content) {
            $("#overlay #content").off("click");
            $("#overlay #content").on("click", function () {
                return false;
            });

            $("#overlay").off("click");
            $("#overlay").on("click", function () {
                $(this).hide();
            });

            $("#overlay #content").html(content);
        },

        show: function () {
            $("#overlay").show();
        },

        hide: function () {
            $("#overlay").hide();
        }
    },

    getNonceStr: function() {
        return "" + new Date().getTime();
    }
});