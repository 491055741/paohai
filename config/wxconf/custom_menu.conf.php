<?php

$menu = array(
    "menu" => array(
        "button" => array(
            array(
                "type" => "click",
                "name" => "制作YOYO",
                "key" => "begin",
                "sub_button" => array(),
            ),
            array( 
                "type" => "click",
                "name" => "怎么玩",
                "key" => "promotion",
                "sub_button" => []
            ),
            array(
                "name" => "我的YOYO", 
                "sub_button" => array(
                    array(
                        "type" => "click",
                        "name" => "我的订单",
                        "key" => "orders",
                        "sub_button" => array(),
                    ),
                    array(
                        "type" => "view",
                        "name" => "地址簿",
                        "url" => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx4a41ea3d983b4538&redirect_uri=http://paohai.ikamobile.com/wxpay/addr&response_type=code&scope=snsapi_base#wechat_redirect",
                        "sub_button" => array(),
                    ),
                ),
            ),
        ),
    ),
);

/* End of file */
