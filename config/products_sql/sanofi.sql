INSERT INTO `image` (`url`, `description`) VALUES
    ('https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAo8ayLbpkicdt8Bib7Og5b6wf02Xjjsm3Zqh5OQFSteWI38s8XSsZRGicickrfndicATFNZMq5MicGfzwBqA/0', 's_v_sanoficover.png'),
    ('https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAo8ayLbpkicdt8Bib7Og5b6wf0iaT4enq0x4nOOOlLic68hFaolibIwhJVgOZwaxTqO6sQjFgo8dMWoHBPw/0', 's_v_sanofi'),
    ('https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAo8ayLbpkicdt8Bib7Og5b6wf0xE2icscTCzXEVuh1IDgn90PqdAibP7licQ0ReGK4iavktBSAKCkyVnzVjw/0', 's_h_sanofi'),
    ('https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAo8ayLbpkicdt8Bib7Og5b6wf0icHZNBNnD53c5Jucsk3QpLm00DibQHxLcxz3JeiazCSdVhTiacL8FibGEjw/0', 'b_v_sanoficover'),
    ('http://mmsns.qpic.cn/mmsns/j8WFfyvBAo8ayLbpkicdt8Bib7Og5b6wf0AR4m3bGLbenJibC1fAq7PkQ/0', 'b_v_sanofi'),
    ('http://mmsns.qpic.cn/mmsns/j8WFfyvBAo8ayLbpkicdt8Bib7Og5b6wf0W8gFMict00tAnSGC5Dul5Gw/0', 'b_h_sanofi');


INSERT INTO `activity_template_config` (`id`, `actId`, `imgId`, `imgThumbId`, `rotate`, `status`) VALUES
    (22, 1, 38, 35, -90, 1),
    (23, 1, 39, 36, -90, 1),
    (24, 1, 40, 37, 0, 1);


-- TODO
UPDATE `activity` SET `templateIdOrder` = '[22,23,24,2,4,17,8,1,3,16,14]' WHERE id = 1;
-- UPDATE `activity` SET `templateIdOrder` = '[22,23,24,2,4,21,8,1,3,20,14]' WHERE id = 1;

INSERT INTO `activity_price_rule` (`type`, `priceConf`) VALUES 
   (1, '{"defaultPrice":299,"step":{"0":{"totalNum":500,"beginTime":"2015-01-29 00:00:00","endTime":"2015-01-30 23:59:59"}}}');
-- INSERT INTO `activity_price_rule` (`type`, `priceConf`) VALUES 
--    (1, '{"defaultPrice":299,"step":{"0":{"totalNum":500,"beginTime":"2015-01-29 00:00:00","endTime":"2015-01-30 23:59:59"}}}');

UPDATE `activity_template_config` SET `priceRuleId` = 1 WHERE id IN (22, 23, 24);
