/**
 * Created by admin on 2014/11/6.
 */
var HC2 = {
    posrcarParams: {
        picUrl: "http://xxxxxxxx",
        offsetX: -0.0017,
        offsetY: 0,
        templateIndex: 2,
        templateRotateIndex: 1,

        // adress
        addressName: "xxxxx",//收件人名字
        address: "xxxxx",//收件人地址
        addressZipcode: "610000",//邮编

        // content
        salutation: "zhangsan",
        content: "hello",
        signature: "lisi",

        // postmark 邮戳的ID
        postmarkIndex: 1
    },
    data : {
        post_em : document.getElementsByClassName("youzhen")[0].getElementsByTagName("em"),
        post_stamp : document.getElementById("post_stamp"),
        addressee_info : document.getElementsByClassName("shoujianInput")[0],
        greeting_info : document.getElementById("liuyan"),
        getJ : document.getElementById("getJ"),
        getZ : document.getElementById("getZ"),
        pop1 : document.getElementsByClassName("pop1")[0],
        pop2 : document.getElementsByClassName("pop2")[0],
        pop3 : document.getElementsByClassName("pop3")[0],
        confirm1 : document.getElementById("pop1_conf"),
        confirm2 : document.getElementById("pop2_conf"),
        confirm3 : document.getElementById("pop3_conf"),
        post_stamp_a : document.getElementsByClassName("youchuoWraper")[0].getElementsByTagName("a")
    },
    /**
     *
     * 页面2逻辑
     *
     * */
    setCardInfo : function(salutation,content,signature,addressName,address,addressZipcode,n){
        var dp = HC2.data;
        var pp = HC2.posrcarParams;

        //页面2中要初始化的信息
        var post_code = addressZipcode || "646200";//邮政编码
        var post_stamp = "images/youchuo"+n+".png" || null;//邮戳
        //收件人信息
        dp.addressee_info.value = signature || "王秋儿";
        //祝福信息
        dp.greeting_info.value = content || "开开心心，快快乐乐！";
        //邮戳
        dp.post_stamp.src = post_stamp;
        //邮编回填
        var postcodeArr = String(post_code).split("");
        for (var i = 0; i < dp.post_em.length;i++) {
             dp.post_em[i].innerHTML = postcodeArr[i];
        }
        //弹窗1
        pp.postmarkIndex = n || pp.postmarkIndex;
        console.log("pp.postmarkIndex:"+pp.postmarkIndex);
        dp.post_stamp_a[pp.postmarkIndex].click();
        //弹窗2

        //弹窗3
    },

    callPop : function () {
        var dp = HC2.data;
        var pp = HC2.posrcarParams;//模拟参数
        EventUtil.addhandler(dp.getJ,"click", function () {//显示弹窗1
            classie.removeClass(dp.pop1,"hide");
            HC2.setCardInfo("","","","","","",pp.postmarkIndex);
        });
        EventUtil.addhandler(dp.getZ,"click", function () {//显示弹窗1
            classie.removeClass(dp.pop1,"hide");
        });
        EventUtil.addhandler(dp.addressee_info,"click", function () {//显示弹窗2
            classie.removeClass(dp.pop2,"hide");
        });


        EventUtil.addhandler(dp.greeting_info,"click", function () { //显示弹窗3
            classie.removeClass(dp.pop3,"hide");
        });

        EventUtil.addhandler(dp.confirm1,"click", function () { //确定弹窗1：邮戳
            classie.addClass(dp.pop1,"hide");
            HC2.setCardInfo("","","","","","",pp.postmarkIndex);
        });
        EventUtil.addhandler(dp.confirm2,"click", function () { //确定弹窗2：收件人信息
            classie.addClass(dp.pop1,"hide");
            HC2.setCardInfo();
        });
        EventUtil.addhandler(dp.confirm1,"click", function () { //确定弹窗3：要祝福的对象的信息
            classie.addClass(dp.pop3,"hide");
            HC2.setCardInfo();
        });
//        for (var i = 0,len = dp.confirm.length ; i < len ;i++){ //点击每个弹出窗的确定按钮，关闭弹出窗
//            EventUtil.addhandler(dp.confirm[i],"click", function () {
//                classie.addClass(this.parentNode,"hide");//?为什么this不能换成dp.confirm[i]
//            });
//        }

        for (var j = 0 , leng = dp.post_stamp_a.length ; j < leng ; j++){
            dp.post_stamp_a[j].index = function(num){
                return function(){
                   return num;
                }()
            }(j);

            EventUtil.addhandler(dp.post_stamp_a[j],"click", function () {
                for (var n = 0 ; n < leng ; n++) {
                    classie.removeClass(dp.post_stamp_a[n],"on");
                }
                classie.addClass(this,"on");
                console.log(this.index);
                pp.postmarkIndex = this.index;//保存最后一次点击值
                return false;
            });
        }
    }
};
HC2.callPop();