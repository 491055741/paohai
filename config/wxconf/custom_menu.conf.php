<?php
/*
 自定义菜单设置数据，到WX自定义页面进行查看和设置, url:
 https://mp.weixin.qq.com/debug/cgi-bin/apiinfo?t=index&type=%E8%87%AA%E5%AE%9A%E4%B9%89%E8%8F%9C%E5%8D%95&form=%E8%87%AA%E5%AE%9A%E4%B9%89%E8%8F%9C%E5%8D%95%E6%9F%A5%E8%AF%A2%E6%8E%A5%E5%8F%A3%20/menu/get
 */

$menu = array(
    "button" => array(
        array(
            "type" => "click",
            "name" => "制作明信片",
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
            "name" => "我的明信片", 
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
                    "url" => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx4a41ea3d983b4538&redirect_uri=" . urlencode("http://paohai.ikamobile.com/wxpay/addr") . "&response_type=code&scope=snsapi_base#wechat_redirect",
                    "sub_button" => array(),
                ),
            ),
        ),
    ),
);

/* End of file */
