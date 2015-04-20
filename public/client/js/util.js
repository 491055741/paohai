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
    },

    getProvinceAndCityFromAddress: function (address) {
        var info = {};

        for (var i = 0, length = Provinces.length; i < length; i++) {
            item = Provinces[i];
            if (new RegExp(item.province).test(address)) {
                info.province = item.province;

                var cities = item.cities.split("|");
                for (var j = 0, jLength = cities.length; j < jLength; j++) {
                    var city = cities[j];
                    if (new RegExp(city).test(address)) {
                        info.city = city;
                        break;
                    }
                }
                break;
            }
        }

        return info;
    }
});