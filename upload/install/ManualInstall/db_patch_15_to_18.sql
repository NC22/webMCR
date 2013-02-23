SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `news` ADD `message_full` MEDIUMTEXT NOT NULL;
ALTER TABLE `accounts` ADD `email` varchar(50) NOT NULL;

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `message` varchar(255) NOT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
