/**
 * Created by admin on 2014/11/6.
 */
var HC2 = {
    data : {
        post_em : document.getElementsByClassName("youzhen")[0].getElementsByTagName("em"),
        post_stamp : document.getElementById("post_stamp"),
        addressee_info : document.getElementsByClassName("shoujianInput")[0],
        greeting_info : document.getElementById("liuyan"),
        getJ : document.getElementById("getJ"),
        getZ : document.getElementById("getZ"),
        pop1 : document.getElementsByClassName("pop1")[0],
        pop2 : document.getElementsByClassName("pop2")[0],
        pop3 : document.getElementsByClassName("pop3")[0]
    },
    /**
     *
     * 页面2逻辑
     *
     * */
    setCardInfo : function(post_code,n,addressee,greetings){
        var dp = HC2.data;
        //模拟数据传入
        addressee.name = "王秋儿";
        post_code = 646200;
        n = 2 ;
        greetings.greet = "好好的，开开心心的，加油！！";

        //要页面2中要初始化的信息
        var post_code = post_code || null;//邮政编码
        var post_stamp = "images/youchuo"+n+".png" || null;//邮戳
        var addressee_info = addressee.name || null;//收件人信息
        var greet = greetings.greet || null;//祝福语

        //收件人信息
        dp.addressee_info.value = addressee_info;
        //祝福信息
        dp.greeting_info.value = greet;
        //邮戳
        dp.post_stamp.src = post_stamp;
        //邮编回填
        var postcodeArr = String(post_code).split("");
        for (var i = 0; i < dp.post_em.length;i++) {
             dp.post_em[i].innerHTML = postcodeArr[i];
        }
    },

    callPop : function () {
        var dp = HC2.data;
        EventUtil.addhandler(dp.getJ,"click", function () {
            classie.removeClass(dp.pop1,"hide");
        });
        EventUtil.addhandler(dp.getZ,"click", function () {
            classie.removeClass(dp.pop1,"hide");
        });
    }
};
HC2.callPop();