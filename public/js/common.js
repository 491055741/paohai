/**
 * Created by admin on 2014/10/31.
 * 页面逻辑主要放在该文件
 */

var HC = {
    touch: {
        pageTitle : document.getElementsByTagName("title")[0],
        /**/
        var_user_picurl : document.getElementById("var-user-picurl")||"",
        var_offset_x : document.getElementById("var-offset-x")||"",
        var_offset_y : document.getElementById("var-offset-y")||"",
        var_template_index : document.getElementById("var-template-index")||"",
        var_template_rotate_index : document.getElementById("var-template-rotate-index")||"",
        /**/
            bg_layer  : document.getElementsByClassName("bgLayer")[0]||"",
            img_layer : document.getElementsByClassName("imgLayer")[0]||"",
            selectTem : document.getElementById("select_temp")||"",
            bgLayer_img : document.getElementsByClassName("bgLayer_img")[0]||"",
            imgLayer_img : document.getElementsByClassName("imgLayer_img")[0]||"",
            thumb : document.getElementsByClassName("thumbImg")[0]||"",
            thumb_ul : document.getElementById("thumb_ul")||"",
            ul_li : document.getElementById("thumb_ul")?document.getElementById("thumb_ul").getElementsByTagName("li"):"",
            ul_imgs : document.getElementsByClassName("thumb_imgs")||"",
            tsx : 0,
            tsy : 0,
            tex : 0,
            tey : 0,
            x : 0,
            y : 0,
            s : false,
        //缩放后的宽高变量 2014-11-6
            pic_w : 0,
            pic_h : 0

    },
    /**
     *
     * 页面1逻辑
     *
     * */
    up : function () { //点击选择模板按钮触发
        var tp = HC.touch;
        var selectTem = tp.selectTem,
            bgL = tp.bg_layer,
            imgL = tp.img_layer,
            bgLayer_img = tp.bgLayer_img,
            thumb = tp.thumb;

        selectTem.onclick = function(){
                classie.toggle(thumb,"up");
                classie.toggle(bgL,"up");
                classie.toggle(imgL,"up");
                classie.toggle(bgLayer_img,"up");
            };
    },
    calWidth : function () { //计算缩略图ul的宽度
        var tp = HC.touch;
        var thumb_ul = tp.thumb_ul,
            ul_li = tp.ul_li;

        var ul_width = thumb_ul.offsetWidth,
            li_width = ( ul_li[0].offsetWidth + 10 ) * ul_li.length;

        if (ul_width < li_width) {
            thumb_ul.style.width = li_width + "px";
        }
    },
    clickImgTemp : function () { //点击模板缩略图动态更换 预览图的背景框
        var tp = HC.touch;
        var ul_imgs = tp.ul_imgs,
            bgLayer_img = tp.bgLayer_img,
            i = 0,
            len = ul_imgs.length;
            console.log(len);
        for ( ; i < len ; i++) {

            ul_imgs[i].index = function(n){
                return function(){
                    return n;
                }();
            }(i);

            ul_imgs[i].onclick = function (e) {
                e.stopPropagation();
                bgLayer_img.src = this.src;
                console.log(this.index);
                HC.rotate(this.index);
            };
        }
    },
    handtouch : function (e) { //
        var tp = HC.touch;
        if(e.touches.length == 1){
            switch(e.type){
                case "touchstart":
                    tp.tsx = e.touches[0].clientX;
                    tp.tsy = e.touches[0].clientY;
                    break;

                case "touchmove":
                    e.preventDefault();
                    tp.tex = parseInt(e.changedTouches[0].clientX);
                    tp.tey = parseInt(e.changedTouches[0].clientY);
                    tp.x = parseInt(e.changedTouches[0].clientX - tp.tsx ) ;
                    tp.y = parseInt( e.changedTouches[0].clientY - tp.tsy);
                    HC.scrolling(tp.x,tp.y);
                    tp.tsx = tp.tex;
                    tp.tsy = tp.tey;
                    break;
            }
        }
    },
    scrolling : function (x,y) { //将预览图滚相应的位移
        var tp = HC.touch;
        var img_layer = HC.touch.img_layer;
        img_layer.scrollTop = img_layer.scrollTop - y;
        img_layer.scrollLeft = img_layer.scrollLeft - x;

        tp.var_offset_x.value = -(img_layer.scrollLeft / tp.pic_w);//offset-x 2014-11-6
        tp.var_offset_y.value = -(img_layer.scrollTop / tp.pic_h);//offset-x 2014-11-6
    },
    scale : function (b) { //缩放
        var tp = HC.touch;
        var pic_orig_w = tp.imgLayer_img.offsetWidth,
            pic_orig_h = tp.imgLayer_img.offsetHeight,
            bg_w = tp.bgLayer_img.offsetWidth,
            bg_h = tp.bgLayer_img.offsetHeight;
        var wRatio, hRatio, temp;

        if ( b ) {
            temp = pic_orig_h; pic_orig_w = pic_orig_h; pic_orig_w = temp;
        }

            wRatio = bg_w / pic_orig_w ;
            hRatio = bg_h / pic_orig_h ;
            var ratio = wRatio > hRatio ? wRatio : hRatio;
            //将缩放后的宽高赋值给变量 2014-11-6
            tp.pic_w = pic_orig_w * ratio;
            tp.pic_h = pic_orig_h * ratio;

            tp.imgLayer_img.style.width = tp.pic_w + "px";
            tp.imgLayer_img.style.height = tp.pic_h + "px";

    },
    rotate : function(selectedTemplateIndex){ //旋转
        var tp = HC.touch;
        console.log("a:"+selectedTemplateIndex);
        console.log(tp.var_template_rotate_index.value);
        if( selectedTemplateIndex > tp.var_template_rotate_index.value ) {
            tp.s = true;
            HC.scale(tp.s);
            tp.imgLayer_img.style.webkitTransform="rotate(90deg)";
            tp.imgLayer_img.style.transform="rotate(90deg)";
        }else{
            tp.s = false ;
            HC.scale(tp.s);
            tp.imgLayer_img.style.webkitTransform="";
            tp.imgLayer_img.style.transform="";
        }
        tp.var_template_index.value = selectedTemplateIndex ;
    },
     init : function () {
        var tp = HC.touch,
            stepNum = tp.pageTitle.innerHTML;
            console.log(stepNum);
             tp.imgLayer_img.src = tp.var_user_picurl.value;
             tp.bgLayer_img.src = tp.ul_imgs[tp.var_template_index.value].src;
             HC.up();
             HC.calWidth();
             HC.clickImgTemp();
             setTimeout(function () {
                 HC.scale();
                 //初始化，是否旋转 2014-11-6
                 tp.ul_imgs[tp.var_template_index.value].click();
                 //初始化，是否位移 2014-11-6
                 tp.img_layer.scrollLeft = (-tp.var_offset_x.value * tp.pic_w);
                 tp.img_layer.scrollTop = (-tp.var_offset_y.value * tp.pic_h);
             },1000);
        }
};

EventUtil.addLoadEvent(HC.init);
EventUtil.addhandler(HC.touch.bg_layer,"touchstart",HC.handtouch);
EventUtil.addhandler(HC.touch.bg_layer,"touchmove",HC.handtouch);
