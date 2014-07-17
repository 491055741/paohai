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

    setTimeout(function() {
        init();
    }, 100);

});

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
        overlay();
        output(" application cache downloading");
    };

    applicationCache.onnoupdate = function() {
        output(" application cache no update");
    };

    applicationCache.onprogress = function() {
        output(" application cache progress");
    };

    applicationCache.oncached = function() {
        closeAllDialog();
        output(" application cache cached");
        //        location.reload(true); // reload the whole web page
    };

    applicationCache.onupdateready = function() {
        output(" application cache update ready");
        closeAllDialog();
        location.reload(true); // reload the whole web page
    };

    applicationCache.onerror = function() {
        closeAllDialog();
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

function overlay() 
{
    if (art.dialog.list["overlay"]) {
        return art.dialog.list["overlay"];
    }
    var originalContentCSS = {};
    var originalMainCSS = {};
    art.dialog({
        id: "overlay",
        padding: 0,
        content: '<img src="./css/images/ajax-loader.gif" />',
        lock: true,
        drag: false,
        resize: false,
        fixed: true,
        init: function() {
            $(".aui_border tbody tr:nth-child(1)").hide();
            $(".aui_border tbody tr:nth-child(3)").hide();
            originalContentCSS.margin = $(".aui_content").css("margin");
            originalContentCSS.padding = $(".aui_content").css("padding");
            $(".aui_content").css({
                "margin": "0",
                "padding": "0"
            });
            originalMainCSS.paddingTop = $(".aui_main").css("padding-top");
            $(".aui_main").css({
                "padding-top": "0"
            });
        },
        close: function () {
            $(".aui_content").css(originalContentCSS);
            $(".aui_main").css(originalMainCSS);
        }
    });

    return art.dialog.list["overlay"];
}

function closeAllDialog()
{
    var list = art.dialog.list;
    for (var i in list) {
        list[i].close();
    }
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