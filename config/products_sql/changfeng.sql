INSERT INTO `image` (`url`, `description`) VALUES
    ('https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAo99uqfau5lU5bibYgO2w1FjPAwdPqM8ZEiayCosGMSiaJbHdjicI2ap7r2fGIYCCqN0Oftib2PaRN26TaA/0', 's_v_cfcover'),
    ('https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAo99uqfau5lU5bibYgO2w1FjPt4aiccIzQyNRU5pAZnbPZ2tbXMqIZPY4ZXLqRAz2icjDpDUTYzRT5S7g/0', 'b_v_cfcover');


INSERT INTO `activity_template_config` (`actId`, `imgId`, `imgThumbId`, `rotate`, `status`) VALUES
    (1, 42, 41, -90, 1);


INSERT INTO `activity_price_rule` (`type`, `priceConf`) VALUES 
    (1, '{"defaultPrice":299,"step":{"0":{"totalNum":1000,"perNum":1,"beginTime":"2015-02-08 00:00:00","endTime":"2015-02-15 23:59:59"}}}');

UPDATE `activity_template_config` SET `priceRuleId` = 2 WHERE id = 25;

UPDATE `activity` SET `templateIdOrder` = '[25,2,4,17,8,1,3,16,14]' WHERE id = 1;
