$(document).ready(function() {
    // var url = window.location.href;
    // var idx = url.indexOf("#"); // if current location is not home page, go to home page when user refresh the page.
    // if (idx != -1) {
    //     window.location = url.substring(0, idx);
    //     return;
    // }
    applicationCacheHandeler();

    $("a.goBack").fastClick(function(e) {
        $.mobile.back();
        e.stopPropagation();
        return false;
    });

    if (typeof(document.referrer) == "undefined") {
        output("document.referrer:" + document.referrer);
        sessionStorage.referrer = document.referrer;
    } else {
        output("browser not support document.referrer.");
    }

    if (typeof WeixinJSBridge == "undefined") {
        if ( document.addEventListener ) {
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        } else if (document.attachEvent) {
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
    } else {
        onBridgeReady();
    }
});

function onBridgeReady() {
    WeixinJSBridge.call('hideOptionMenu');
}

$.ajaxSetup({
    cache: false
});

$(document).bind("mobileinit", function() {

    $.mobile.loadingMessage = '页面载入中';
    $.mobile.pageLoadErrorMessage = '页面载入失败';
    $.mobile.transitionFallbacks.slideout = "none";
    // jquery mobile used $.ajax() to load page for using page transition,
    // in jquery, $.ajax() method set cache option default by true, but in Android platform this will cause some problems, if loaded from cache
    // ajax request event will not be fired.
    // in order to improve the speed of loading resources, HTML5 feature application cache must to be used.
    // to fix this issue , set cache option to false before jquery mobile setting up.
    //  var agent = navigator.userAgent.toLowerCase();
    //  if (agent.match(/android/i) == "android") {
    $.ajaxSetup({
        cache: false,
        headers: {
            "Cache-Control": "no-cache"
        }
    });
    //  }

});

function applicationCacheHandeler() {
    applicationCache.onchecking = function() {
        output(" application cache checking");
    };

    applicationCache.ondownloading = function() {
        output(" application cache downloading");
    };

    applicationCache.onnoupdate = function() {
        output(" application cache no update");
    };

    applicationCache.onprogress = function() {
        output(" application cache progress");
    };

    applicationCache.oncached = function() {
        output(" application cache cached");
        //        location.reload(true); // reload the whole web page
    };

    applicationCache.onupdateready = function() {
        output(" application cache update ready");
        location.reload(true); // reload the whole web page
    };

    applicationCache.onerror = function() {
        output(" application cache error");
    };
}

function isAndroid() {
    var agent = navigator.userAgent.toLowerCase(); // on Android, when click on 'input', it doesn't scroll up automatic.
    if (agent.match(/android/i) != "android") {
        return false;
    }
    return true;
}

function clearStorage() {
    for (var i = 0, len = sessionStorage.length; i < len; i++) {
        var key = sessionStorage.key(i);
        var value = sessionStorage.getItem(key);
        output("removing " + key + " : " + value);
        sessionStorage.removeItem(key); /// ?????
    }
}

function output(text) {
    console.log(text);
}

function changePage(pageName) {
    if (isAndroid()) {
        $.mobile.changePage($(pageName), {
            transition: "none"
        });
    } else {
        $.mobile.changePage($(pageName), {
            transition: "slidefade"
        });
    }
}