/**
 * Created by hc on 2014/10/29.
 * 页面逻辑主要放在该文件
 */
var HC = {
    addLoadEvent : function (fn,data) { //加载函数，有待改进
        var oldLoadFn = window.onload;
        if (typeof oldLoadFn != "function") {
            window.onload = fn(data);
        }else{
            window.onload = function () {
                oldLoadFn();
                fn(data);
            };
        }
    },
    up : function () { //点击选择模板按钮触发
        var selectTem = document.getElementById("select_temp"),
            bgL = document.getElementsByClassName("bgLayer")[0],
            imgL = document.getElementsByClassName("imgLayer")[0],
            bgLayer_img = document.getElementsByClassName("bgLayer_img")[0],
            thumb = document.getElementsByClassName("thumbImg")[0];

        selectTem.onclick = function(){
                classie.toggle(thumb,"up");
                classie.toggle(bgL,"up");
                classie.toggle(imgL,"up");
                classie.toggle(bgLayer_img,"up");
            }
    },
    calWidth : function () { //计算缩略图ul的宽度
        var thumb_ul = document.getElementById("thumb_ul"),
            ul_li = thumb_ul.getElementsByTagName("li");

        var ul_width = thumb_ul.offsetWidth,
            li_width = ( ul_li[0].offsetWidth + 10 ) * ul_li.length;

        if (ul_width < li_width) {
            thumb_ul.style.width = li_width + "px";
        }
    },
    clickImgTemp : function () { //点击模板缩略图动态更换 预览图的背景框
        var ul_imgs = document.getElementsByClassName("thumb_imgs"),
            bgLayer_img = document.getElementsByClassName("bgLayer_img")[0],
            i = 0,
            len = ul_imgs.length;

        for ( ; i < len ; i++) {
            ul_imgs[i].onclick = function () {
                bgLayer_img.src = this.src;
            };
        }
    }
};

HC.addLoadEvent(HC.calWidth);
HC.addLoadEvent(HC.up);
HC.addLoadEvent(HC.clickImgTemp);

