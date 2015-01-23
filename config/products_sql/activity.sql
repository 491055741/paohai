--
-- image table
--
CREATE TABLE `image` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `url` varchar(512) NOT NULL,
    `description` varchar(128) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- activity config table 
--
CREATE TABLE `activity` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'auto_increment begin 101, 1 is reserved for default activity',
    `startTime` DATETIME NOT NULL,
    `endTime` DATETIME NOT NULL,
    `templateIdOrder` VARCHAR(512) NOT NULL DEFAULT '',
    `priceRuleId` INT(11) UNSIGNED DEFAULT NULL COMMENT 'activity global price rule config id, if template not specify price rule config id, use it',
    `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-close; 1-open;',
    PRIMARY KEY (`id`),
    KEY `statusTime` (`startTime`, `endTime`, `status`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;


--
-- activity template price rule config
--
CREATE TABLE `activity_price_rule` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` TINYINT(4) UNSIGNED NOT NULL COMMENT 'price rule type, referer to activity setting file',
    `priceConf` text DEFAULT '' COMMENT 'price rule',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- activity template config
--
CREATE TABLE `activity_template_config` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `actId` INT(11) UNSIGNED NOT NULL,
    `imgId` INT(11) UNSIGNED NOT NULL,
    `imgThumbId` INT(11) UNSIGNED NOT NULL,
    `rotate` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'rotate angle, clockwise',
    `priceRuleId` INT(11) UNSIGNED DEFAULT NULL COMMENT 'activity price rule config id, if not null, ignore global activity price rule',
    `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-unused; 1-used',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`actId`) REFERENCES `activity` (`id`),
    FOREIGN KEY (`imgId`) REFERENCES `image` (`id`),
    FOREIGN KEY (`priceRuleId`) REFERENCES `activity_price_rule` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- user join activity record
--
CREATE TABLE `activity_join_record` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `userName` VARCHAR(32) DEFAULT NULL COMMENT 'user openid of wx',
    `actId` INT(11) UNSIGNED NOT NULL,
    `orderId` varchar(12) NOT NULL DEFAULT '',
    `joinTime` DATETIME NOT NULL,
    `price` INT(11) UNSIGNED DEFAULT '0',
    `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-uncomplete; 1-complete; only the order wx notify payed finished or price equal zero can be set 1',
    PRIMARY KEY (`id`),
    UNIQUE KEY `orderId` (`orderId`),
    KEY `actUser` (`actId`, `joinTime`, `userName`),
    FOREIGN KEY (`actId`) REFERENCES `activity` (`id`),
    FOREIGN KEY (`orderId`) REFERENCES `order_table` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `order_table` ADD `activityId` INT(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'referer to field: id of table activity. 1 for default activity';
