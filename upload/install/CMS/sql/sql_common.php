<?php 
/* SQL COMMON TABLES CREATE + ADD DEFAULT INFO + UPDATES */

BD("SET FOREIGN_KEY_CHECKS=0;");

if ($mysql_rewrite) {

BD("DROP TABLE IF EXISTS `{$bd_names['ip_banning']}`,
						 `{$bd_names['news']}`,
						 `{$bd_names['news_categorys']}`,
						 `{$bd_names['likes']}`,
                         `{$bd_names['groups']}`,
						 `{$bd_names['data']}`,
						 `{$bd_names['comments']}`,
						 `{$bd_names['files']}`,
                         `{$bd_names['servers']}`;");
}

/* CREATE TABLES */
	
BD("CREATE TABLE IF NOT EXISTS `{$bd_names['likes']}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `item_type` smallint(3) NOT NULL DEFAULT 1,
  `var` tinyint(1) NOT NULL DEFAULT -1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['files']}` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['news']}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) NOT NULL DEFAULT 1,
  `user_id` bigint(20) NOT NULL,
  `dislikes` int(10) DEFAULT 0,
  `likes` int(10) DEFAULT 0,
  `hide_vote` tinyint(1) NOT NULL DEFAULT 0,
  `hits` int(10) DEFAULT 0,
  `title` char(255) NOT NULL,
  `message` TEXT NOT NULL,
  `message_full` MEDIUMTEXT NOT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['news_categorys']}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `priority` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;");

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['servers']}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `online` tinyint(1) DEFAULT 0,
  `rcon` varchar(255) DEFAULT '',
  `service_user` char(64) default NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['groups']}` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100;");

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['comments']}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `message` varchar(255) NOT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['ip_banning']}` (
  `IP` varchar(16) NOT NULL,
  `time_start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ban_until` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ban_type` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['data']}` (
  `property` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  UNIQUE KEY `property` (`property`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

/* DEFAULT INFO ADD */

BD("INSERT INTO `{$bd_names['news_categorys']}` (`id`,`name`) VALUES (1,'Без категории');");

BD("INSERT INTO `{$bd_names['data']}` (`property`, `value`) VALUES
('latest-game-build', '10746'),
('launcher-version', '13'),
('next-reg-time', '2'),
('email-verification', '0'),
('rcon-port', '0'),
('rcon-pass', '0'),
('rcon-serv', '0');");

BD("INSERT INTO `{$bd_names['data']}` (`property`, `value`) VALUES
('smtp-user', ''),
('smtp-pass', ''),
('smtp-host', 'localhost'),
('smtp-port', '25'),
('smtp-hello', 'HELO'),
('game-link-win', ''),
('game-link-osx', ''),
('game-link-lin', '');");

BD("INSERT INTO `{$bd_names['data']}` (`property`, `value`) VALUES
('email-name', 'Info'),
('email-mail', 'noreplay@noreplay.ru');");

/* 2.05 UPDATE */

if (!BD_ColumnExist($bd_names['ip_banning'], 'ban_type'))

BD("ALTER TABLE `{$bd_names['ip_banning']}` 
	ADD `ban_type` tinyint(1) NOT NULL DEFAULT 1,
	ADD `reason` varchar(255) DEFAULT NULL;");
	
/* 2.1 UPDATE */

if (!BD_ColumnExist($bd_names['news'], 'user_id')) {

BD("ALTER TABLE `{$bd_names['news']}` 
	ADD `user_id` bigint(20) NOT NULL,
	ADD `dislikes` int(10) DEFAULT 0,
	ADD `likes` int(10) DEFAULT 0;");
	
BD("ALTER TABLE `{$bd_names['news']}`	ADD KEY `category_id` (`category_id`),
										ADD KEY `user_id` (`user_id`);");
					
BD("ALTER TABLE `{$bd_names['comments']}`	ADD KEY `user_id` (`user_id`),
											ADD	KEY `item_id` (`item_id`);");

BD("ALTER TABLE `{$bd_names['users']}`	ADD	KEY `group_id` (`{$bd_users['group']}`);");	
}	

/* 2.15 UPDATE */
if (!BD_ColumnExist($bd_names['users'], $bd_users['deadtry'])) {

BD("ALTER TABLE `{$bd_names['users']}`	ADD `{$bd_users['deadtry']}` tinyint(1) DEFAULT 0;");	
}

/* 2.25b UPDATE */
if (!BD_ColumnExist($bd_names['users'], $bd_users['clientToken'])) {

BD("ALTER TABLE `{$bd_names['users']}` ADD `{$bd_users['clientToken']}` varchar(255) DEFAULT NULL;");	
}

/* 2.3 UPDATE */
if (!BD_ColumnExist($bd_names['servers'], 'service_user')) {

BD("ALTER TABLE `{$bd_names['servers']}` ADD `service_user` char(64) default NULL;");
BD("ALTER TABLE `{$bd_names['news']}` ADD `hits` int(10) DEFAULT 0;");	
}

if (!BD_ColumnExist($bd_names['news'], 'hide_vote'))

BD("ALTER TABLE `{$bd_names['news']}` ADD `hide_vote` tinyint(1) NOT NULL DEFAULT 0;");	

/* 2.31 UPDATE */
if (!BD_ColumnExist($bd_names['comments'], 'item_type')) {

BD("ALTER TABLE `{$bd_names['comments']}` ADD `item_type` smallint(3) DEFAULT ". ItemType::News .";");
BD("ALTER TABLE `{$bd_names['comments']}` DROP KEY `item_id`");
BD("ALTER TABLE `{$bd_names['comments']}` ADD KEY `uniq_item` (`item_id`, `item_type`);");

BD("ALTER TABLE `{$bd_names['news']}` CHANGE COLUMN `hide_vote` `vote` tinyint(1) NOT NULL DEFAULT 1;");
BD("ALTER TABLE `{$bd_names['news']}` ADD `discus` tinyint(1) NOT NULL DEFAULT 1;");
BD("ALTER TABLE `{$bd_names['news']}` ADD `comments` int(10) NOT NULL DEFAULT 0;");
}

BD("CREATE TABLE IF NOT EXISTS `{$bd_names['action_log']}` (
  `IP` varchar(16) NOT NULL,
  `first_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `query_count` int(10) NOT NULL DEFAULT 1,
  `info` varchar(255) NOT NULL,
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");