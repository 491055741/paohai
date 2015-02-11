INSERT INTO `image` (`url`, `description`) VALUES
    ('https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoic7PApxO8FCE2l839RGAWSo4WWS6K4gnyiafmSE9aqNldmWlp6H4T18O06C30l9d1lRnf6awGFNlkw/0', 's_v_valentine'),
    ('https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoic7PApxO8FCE2l839RGAWSoGnovAjvkoOlY7gwfibDTs7H5I3K76oS5JtgrkVYHiceCRO4GCjobicqeg/0', 's_h_valentine'),
    ('http://mmsns.qpic.cn/mmsns/j8WFfyvBAoic7PApxO8FCE2l839RGAWSoibmylMm66icpJbcYic8LHv6kg/0', 'b_v_valentine'),
    ('http://mmsns.qpic.cn/mmsns/j8WFfyvBAoic7PApxO8FCE2l839RGAWSohOGpguzLwd8Lqic7b671OvA/0', 'b_h_valentine');


INSERT INTO `activity_template_config` (`actId`, `imgId`, `imgThumbId`, `rotate`, `status`) VALUES
    (1, 34, 32, 0, 1),
    (1, 33, 31, -90, 1);

UPDATE `activity_template_config` SET status = 0 WHERE actId = 1;
UPDATE `activity_template_config` SET status = 1 WHERE id IN (1, 3, 16, 8, 2, 4, 17, 14);
UPDATE `activity` SET `templateIdOrder` = '[2,4,17,8,1,3,16,14]' WHERE id = 1;





--INSERT INTO `activity` (`id`, `startTime`, `endTime`, `templateIdOrder`, `priceRuleId`, `status`) VALUES
--    (101, '2015-02-14 00:00:00', '2015-03-14 00:00:00', '[2,4,17]', 3, 1);

--INSERT INTO `activity_template_config` (`actId`, `imgId`, `imgThumbId`, `rotate`, `status`) VALUES
--    ();

INSERT INTO `activity_price_rule` (`id`, `type`, `priceConf`) VALUES
    (3, 2, '{"defaultPrice":299,"rule":{"0":{"baseNum":1,"beginTime":"2015-02-14 00:00:00","endTime":"2015-03-14 23:59:59"}}}'); 
