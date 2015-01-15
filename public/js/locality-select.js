(function() {
    var where = new Array(32);

    window.LocalitySelection = {

        comefrom : function (loca, locality) {
            this.loca = loca; this.locality = locality;
        },

        select : function () {
            with (document.creator.province) {
                if (selectedIndex == -1)
                    return;
                var loca2 = options[selectedIndex].value;
            }
            for (i = 0;i < where.length; i++) {
                if (where[i].loca == loca2) {
                    loca3 = (where[i].locality).split("|");
                    for (j = 0; j < loca3.length; j++) {
                        with (document.creator.city) {
                            length = loca3.length; options[j].text = loca3[j]; options[j].value = loca3[j];
                        }
                    }
                    break;
                }
            }
        },

        initSelect : function () {
            where[0]  = new LocalitySelection.comefrom("省份", "城市");
            where[1]  = new LocalitySelection.comefrom("北京市", "东城区|西城区|崇文区|宣武区|朝阳区|丰台区|石景山区|海淀区|门头沟|房山区|通州区|顺义区|昌平区|大兴区|平谷区|怀柔区|密云区|延庆县");
            where[2]  = new LocalitySelection.comefrom("上海市", "黄浦区|卢湾区|徐汇区|长宁区|静安区|普陀区|闸北区|虹口区|杨浦区|闵行区|宝山区|嘉定区|浦东新区|金山区|松江区|青浦区|南汇区|奉贤区|崇明县");
            where[3]  = new LocalitySelection.comefrom("天津市", "和平区|东丽区|河东区|西青区|河西区|津南区|南开区|北辰区|河北区|武清区|红挢区|塘沽区|汉沽区|大港区|宁河县|静海县|宝坻|蓟县");
            where[4]  = new LocalitySelection.comefrom("重庆市", "万州区|涪陵区|渝中区|大渡口区|江北区|沙坪坝区|九龙坡区|南岸区|北碚区|万盛区|双挢区|渝北区|巴南区|黔江区|长寿区|綦江区|潼南县|铜梁县|大足区|荣昌县|壁山县|梁平县|城口县|丰都县|垫江县|武隆县|忠县|开县|云阳县|奉节县|巫山县|巫溪县|石柱县|秀山县|酉阳县|彭水县|江津区|合川区|永川区|南川区");
            where[5]  = new LocalitySelection.comefrom("河北省", "石家庄市|邯郸市|邢台市|保定市|张家口市|承德市|廊坊|唐山|秦皇岛|沧州|衡水");
            where[6]  = new LocalitySelection.comefrom("山西省", "太原市|大同市|阳泉市|长治市|晋城市|朔州市|吕梁市|忻州市|晋中市|临汾市|运城市");
            where[7]  = new LocalitySelection.comefrom("内蒙古","呼和浩特市|包头市|乌海市|赤峰市|呼伦贝尔市|阿拉善盟|哲里木盟|兴安盟|乌兰察布市|锡林郭勒盟|巴彦淖尔市|伊克昭盟|通辽市");
            where[8]  = new LocalitySelection.comefrom("辽宁省", "沈阳市|大连市|鞍山市|抚顺市|本溪市|丹东市|锦州市|营口市|阜新市|辽阳市|盘锦市|铁岭市|朝阳市|葫芦岛市");
            where[9]  = new LocalitySelection.comefrom("吉林省", "长春市|吉林市|四平市|辽源市|通化市|白山市|松原市|白城市|延边州");
            where[10] = new LocalitySelection.comefrom("黑龙江省","哈尔滨市|齐齐哈尔市|牡丹江市|佳木斯市|大庆市|绥化市|鹤岗市|鸡西市|黑河市|双鸭山市|伊春市|七台河市|大兴安岭地区");
            where[11] = new LocalitySelection.comefrom("江苏省", "南京市|镇江市|苏州市|南通市|扬州市|盐城市|徐州市|连云港市|常州市|无锡市|宿迁市|泰州市|淮安市");
            where[12] = new LocalitySelection.comefrom("浙江省", "杭州市|宁波市|温州市|嘉兴市|湖州市|绍兴市|金华市|衢州市|舟山市|台州市|丽水市");
            where[13] = new LocalitySelection.comefrom("安徽省", "合肥市|芜湖市|蚌埠市|马鞍山市|淮北市|铜陵市|安庆市|黄山市|滁州市|宿州市|池州市|淮南市|巢湖市|阜阳市|六安市|宣城市|亳州市");
            where[14] = new LocalitySelection.comefrom("福建省", "福州市|厦门市|莆田市|三明市|泉州市|漳州市|南平市|龙岩市|宁德市");
            where[15] = new LocalitySelection.comefrom("江西省", "南昌市|景德镇市|九江市|鹰潭市|萍乡市|新余市|赣州市|吉安市|宜春市|抚州市|上饶市");
            where[16] = new LocalitySelection.comefrom("山东省", "济南市|青岛市|淄博市|枣庄市|东营市|烟台市|潍坊市|济宁市|泰安市|威海市|日照市|莱芜市|临沂市|德州市|聊城市|滨州市|菏泽市");
            where[17] = new LocalitySelection.comefrom("河南省", "郑州市|开封市|洛阳市|平顶山市|安阳市|鹤壁市|新乡市|焦作市|濮阳市|许昌市|漯河市|三门峡市|南阳市|商丘市|信阳市|周口市|驻马店市|济源市");
            where[18] = new LocalitySelection.comefrom("湖北省", "武汉市|宜昌市|荆州市|襄樊市|黄石市|荆门市|黄冈市|十堰市|恩施市|潜江市|天门市|仙桃市|随州市|咸宁市|孝感市|鄂州市|神农架林区");
            where[19] = new LocalitySelection.comefrom("湖南省", "长沙市|常德市|株洲市|湘潭市|衡阳市|岳阳市|邵阳市|益阳市|娄底市|怀化市|郴州市|永州市|湘西州|张家界市");
            where[20] = new LocalitySelection.comefrom("广东省", "广州市|深圳市|珠海市|汕头市|东莞市|中山市|佛山市|韶关市|江门市|湛江市|茂名市|肇庆市|惠州市|梅州市|汕尾市|河源市|阳江市|清远市|潮州市|揭阳市|云浮市");
            where[21] = new LocalitySelection.comefrom("广西", "南宁市|柳州市|桂林市|梧州市|北海市|防城港市|钦州市|贵港市|玉林市|南宁市|柳州市|贺州市|百色市|河池市");
            where[22] = new LocalitySelection.comefrom("海南省", "海口市|三亚市|文昌市|五指山市|临高县|澄迈县|定安县|屯昌县|昌江县|白沙县|琼中县|陵水县|保亭县|乐东县|三沙市|琼海市|万宁市|东方市|儋州市");
            where[23] = new LocalitySelection.comefrom("四川省", "成都市|绵阳市|德阳市|自贡市|攀枝花市|广元市|内江市|乐山市|南充市|宜宾市|广安市|达川市|雅安市|眉山市|甘孜州|凉山州|阿坝州|泸州市");
            where[24] = new LocalitySelection.comefrom("贵州省", "贵阳市|六盘水市|遵义市|安顺市|铜仁市|黔西南州|毕节市|黔东南州|黔南州");
            where[25] = new LocalitySelection.comefrom("云南省", "昆明市|大理州|曲靖市|玉溪市|昭通市|楚雄州|红河州|文山州|思茅市|西双版纳州|保山市|德宏州|丽江市|怒江州|迪庆州|临沧市");
            where[26] = new LocalitySelection.comefrom("西藏", "拉萨市|日喀则地区|山南地区|林芝地区|昌都地区|阿里地区|那曲地区");
            where[27] = new LocalitySelection.comefrom("陕西省", "西安市|宝鸡市|咸阳市|铜川市|渭南市|延安市|榆林市|汉中市|安康市|商洛市");
            where[28] = new LocalitySelection.comefrom("甘肃省", "兰州市|嘉峪关市|金昌市|白银市|天水市|酒泉市|张掖市|武威市|定西市|陇南市|平凉市|庆阳市|临夏市|甘南市");
            where[29] = new LocalitySelection.comefrom("宁夏", "银川市|石嘴山市|吴忠市|固原市|中卫市");
            where[30] = new LocalitySelection.comefrom("青海省", "西宁市|海东地区|海南州|海北州|黄南州|玉树州|果洛州|海西州");
            where[31] = new LocalitySelection.comefrom("新疆", "五家渠市|阿拉尔市|图木舒克市|乌鲁木齐市|克拉玛依市|石河子市|吐鲁番地区|哈密地区|和田地区|阿克苏地区|喀什地区|克孜勒苏州|巴音郭楞州|昌吉州|博尔塔拉州|伊犁州|塔城地区|阿勒泰地区|阿拉山口口岸");
//            where[32] = new LocalitySelection.comefrom("香港", "");
//            where[33] = new LocalitySelection.comefrom("澳门", "");
            // where[34] = new comefrom("台湾","|台北|高雄|台中|台南|屏东|南投|云林|新竹|彰化|苗栗|嘉义|花莲|桃园|宜兰|基隆|台东|金门|马祖|澎湖");

            with (document.creator.province) {
//                onchange="LocalitySelection.select()";
                length = where.length;
                for (k = 0; k < where.length; k++) {
                    options[k].text = where[k].loca;
                    options[k].value = where[k].loca;
                }
                options[selectedIndex].text = where[0].loca; options[selectedIndex].value = where[0].loca;
            }

            with (document.creator.city) {
                loca3 = (where[0].locality).split("|");
                length = loca3.length;
                for (l = 0; l < length; l++) {
                    options[l].text = loca3[l]; options[l].value = loca3[l];
                }
                options[selectedIndex].text = loca3[0]; options[selectedIndex].value = loca3[0];
            }
        },

        selectWithAddress : function (address) {
            var shortAddress = address;
            with (document.creator.province) {
                for (k = 0; k < where.length; k++) {
                    if (address.indexOf(where[k].loca) != -1) {
                        selectedIndex = k;
                        shortAddress = address.substring(where[k].loca.length, address.length - where[k].loca.length);
                        LocalitySelection.select();
                        loca3 = (where[k].locality).split("|");
                        for (l = 0; l < loca3.length; l++) {
                            if (shortAddress.indexOf(loca3[l]) != -1) {
                                document.creator.city.selectedIndex = l;
                                shortAddress = shortAddress.substring(loca3[l].length, shortAddress.length - loca3[l].length);
                                break;
                            }
                        }
                        break;
                    }
                }
            }

            return shortAddress;
        }
    }
})();