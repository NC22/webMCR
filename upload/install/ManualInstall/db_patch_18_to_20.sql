SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `news` ADD `category_id` int(10) NOT NULL DEFAULT 1;
ALTER TABLE `news` ADD `user_id` bigint(20) NOT NULL;
ALTER TABLE `news` ADD `dislikes` int(10) DEFAULT 0;
ALTER TABLE `news` ADD `likes` int(10) DEFAULT 0;
ALTER TABLE `news` ADD `hits` int(10) DEFAULT 0;
ALTER TABLE `news` ADD `hide_vote` tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE `accounts` ADD `female` tinyint(1) NOT NULL DEFAULT '2';
ALTER TABLE `accounts` ADD `deadtry` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `accounts` ADD `group` int(10) NOT NULL DEFAULT 1;
ALTER TABLE `accounts` ADD `comments_num` int(10) NOT NULL;
ALTER TABLE `accounts` ADD `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `accounts` ADD `active_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `accounts` ADD `play_times` int(10) NOT NULL;
ALTER TABLE `accounts` ADD `undress_times` int(10) NOT NULL;
ALTER TABLE `accounts` ADD `default_skin` tinyint(1) NOT NULL DEFAULT '2';

ALTER TABLE `accounts` ADD `clientToken` varchar(255) default NULL;

ALTER TABLE `accounts` DROP `lvl`;

ALTER TABLE `ip_banning` ADD `ban_type` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `ip_banning` ADD `reason` varchar(255) DEFAULT NULL;

ALTER TABLE `news`	ADD KEY `category_id` (`category_id`),
					ADD KEY `user_id` (`user_id`);
					
ALTER TABLE `comments`	ADD	KEY `user_id` (`user_id`),
						ADD	KEY `item_id` (`item_id`);

ALTER TABLE `accounts`	ADD	KEY `group_id` (`group`);

INSERT INTO `data` (`property`, `value`) VALUES
('next-reg-time', '2'),
('email-verification', '0'),
('rcon-port', '0'),
('rcon-pass', '0'),
('rcon-serv', '0');

INSERT INTO `property` (`property`, `value`) VALUES
('smtp-user', ''),
('smtp-pass', ''),
('smtp-host', 'localhost'),
('smtp-port', '25'),
('smtp-hello', 'HELO'),
('email-name', 'Info'),
('email-mail', 'noreplay@noreplay.ru'),
('game-link-win', ''),
('game-link-osx', ''),
('game-link-lin', '');

CREATE TABLE IF NOT EXISTS `likes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `item_type` smallint(3) NOT NULL DEFAULT 1,
  `var` tinyint(1) NOT NULL DEFAULT -1,
  PRIMARY KEY (`id`),
  KEY `uniq_item` (`user_id`,`item_id`,`item_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `action_log` (
  `IP` varchar(16) NOT NULL,
  `first_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `query_count` int(10) NOT NULL DEFAULT 1,
  `info` varchar(255) NOT NULL,
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news_categorys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `priority` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `news_categorys` (`id`,`name`) VALUES (1,'Без категории'); 

CREATE TABLE IF NOT EXISTS `groups` (
  `id`      int(10) NOT NULL AUTO_INCREMENT,
  `name`   char(64) NOT NULL,
  `lvl`     int(10) NOT NULL DEFAULT 1,
  `system` tinyint(1) NOT NULL DEFAULT 0,
  `change_skin` tinyint(1) NOT NULL DEFAULT 0,
  `change_pass` tinyint(1) NOT NULL DEFAULT 0,
  `change_login` tinyint(1) NOT NULL DEFAULT 0,
  `change_cloak` tinyint(1) NOT NULL DEFAULT 0,
  `add_news` tinyint(1) NOT NULL DEFAULT 0,
  `add_comm` tinyint(1) NOT NULL DEFAULT 0,
  `adm_comm` tinyint(1) NOT NULL DEFAULT 0,
  `max_fsize` int(10) NOT NULL DEFAULT 20,  
  `max_ratio` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100;

INSERT INTO `groups` 
(`id`,`name`,`lvl`,`system`,`change_skin`,`change_pass`,`change_login`,`change_cloak`,`add_news`,`add_comm`,`adm_comm`) VALUES 
(1,'Пользователь',2,1,1,1,0,0,0,1,0), 
(2,'Заблокированный',0,1,0,0,0,0,0,0,0), 
(3,'Администратор',15,1,1,1,1,1,1,1,1), 
(4,'Непроверенный',1,1,0,0,0,0,0,0,0), 
(5,'VIP Игрок',5,0,1,1,1,1,0,1,0);

CREATE TABLE IF NOT EXISTS `servers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `online` tinyint(1) DEFAULT 0,
  `rcon` varchar(255) DEFAULT '',
  `players` text default NULL,
  `method` tinyint(1) DEFAULT 0,
  `address` varchar(255) default NULL,
  `port` int(10) DEFAULT 25565,
  `name` varchar(255) default NULL,
  `info` char(255) default NULL,
  `numpl` char(32) default NULL,
  `slots` char(32) default NULL,
  `main_page` tinyint(1) DEFAULT 0,
  `news_page` tinyint(1) DEFAULT 0,
  `stat_page` tinyint(1) DEFAULT 0,
  `priority` tinyint(1) DEFAULT 0,
  `main` tinyint(1) DEFAULT 0,
  `refresh_time` smallint(3) NOT NULL DEFAULT '5',
  `service_user` char(64) default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_word` char(255) DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  `way` char(255) DEFAULT NULL,
  `name` char(255) DEFAULT NULL,
  `dislikes` int(10) DEFAULT 0,
  `likes` int(10) DEFAULT 0,
  `downloads` int(10) DEFAULT 0,
  `size` char(32) DEFAULT 0,
  `hash` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;