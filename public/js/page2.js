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
    setCardInfo : function(){
        var dp = HC2.data;
        var pp = HC2.posrcarParams;
        var receiptInfo = order.getPostcard().getReceiptAddress();
        var messageInfo = order.getPostcard().getMessage(); 
        var postmarkIndex = order.getPostcard().getPostmarkIndex();

        //页面2中要初始化的信息
        // 称呼
        dp.addressee_info.value = messageInfo.getSalutation() || "";
        //祝福信息
        dp.greeting_info.value = messageInfo.getContent() || "";
        //邮戳
        //var post_stamp = "/images/youchuo"+n+".png" || null;//邮戳
        dp.post_stamp.src = "/images/youchuo"+ postmarkIndex +".png";

        //邮编回填
        var post_code = receiptInfo.getZipcode() || "000000";//邮政编码
        var postcodeArr = String(post_code).split("");
        for (var i = 0; i < dp.post_em.length;i++) {
             dp.post_em[i].innerHTML = postcodeArr[i];
        }

        //弹窗1
        pp.postmarkIndex = postmarkIndex || pp.postmarkIndex;
        console.log("pp.postmarkIndex:"+pp.postmarkIndex);
        dp.post_stamp_a[pp.postmarkIndex].click();

        //弹窗2
        $(".pop2 .to_who").val(receiptInfo.getName());
        $(".pop2 .postcode").val(receiptInfo.getZipcode());
        $(".pop2 .to_address").val(receiptInfo.getAddress());

        //弹窗3
        $(".pop3 .to_who").val(messageInfo.getSalutation());
        $(".pop3 .liuyan").val(messageInfo.getContent());
        $(".pop3 .myName").val(messageInfo.getSignature());
    },

    callPop : function () {
        var dp = HC2.data;
        var pp = HC2.posrcarParams;//模拟参数
        EventUtil.addhandler(dp.getJ,"click", function () {//显示弹窗1
            classie.removeClass(dp.pop1,"hide");
            HC2.setCardInfo();
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
            // TODO set postcard object

            classie.addClass(dp.pop1,"hide");
            HC2.setCardInfo();
        });
        EventUtil.addhandler(dp.confirm2,"click", function () { //确定弹窗2：收件人信息
            // TODO set postcard object

            classie.addClass(dp.pop2,"hide");
            HC2.setCardInfo();
        });
        EventUtil.addhandler(dp.confirm1,"click", function () { //确定弹窗3：要祝福的对象的信息
            // TODO set postcard object

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

$(function() {
    // init data
    order.getPostcard().setPostmarkIndex($("#var-postmark-index").val());
    order.getPostcard().getReceiptAddress().setVars({
        name: $("#var-recipient").val(),
        address: $("#var-address").val(),
        zipcode: $("#var-zipcode").val(),
    });
    order.getPostcard().getMessage().setVars({
        salutation: $("#var-salutation").val(),
        content: $("#var-message").val(),
        signature: $("#var-signature").val(),
    });
    HC2.callPop();
    HC2.setCardInfo();
});
