--
-- [ВНИМАНИЕ] При импорте полностью ПЕРЕСОЗДАЁТСЯ вся необходимая структура таблиц
--

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `accounts`,`ip_banning`,`news`,`news_categorys`,`groups`,`data`,`comments`,`servers`;
	
CREATE TABLE IF NOT EXISTS `action_log` (
  `IP` varchar(16) NOT NULL,
  `first_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `query_count` int(10) NOT NULL DEFAULT 1,
  `info` varchar(255) NOT NULL,
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	
CREATE TABLE IF NOT EXISTS `likes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `item_type` smallint(3) NOT NULL DEFAULT 1,
  `var` tinyint(1) NOT NULL DEFAULT -1,
  PRIMARY KEY (`id`),
  KEY `uniq_item` (`user_id`,`item_id`,`item_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
	
CREATE TABLE IF NOT EXISTS `news` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) NOT NULL DEFAULT 1,
  `user_id` bigint(20) NOT NULL,
  `dislikes` int(10) DEFAULT 0,
  `likes` int(10) DEFAULT 0,
  `title` char(255) NOT NULL,
  `vote` tinyint(1) NOT NULL DEFAULT 0,
  `discus` tinyint(1) NOT NULL DEFAULT 1,
  `comments` int(10) NOT NULL DEFAULT 0,
  `message` text NOT NULL,
  `message_full` mediumtext NOT NULL,
  `time` datetime DEFAULT NULL,  
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `news_categorys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `priority` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `news_categorys` (`id`,`name`) VALUES (1,'Без категории'); 

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

CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `message` varchar(255) NOT NULL,
  `item_type` smallint(3) DEFAULT 1,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `uniq_item` (`item_id`, `item_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `login` char(32) DEFAULT NULL,
  `female` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(50) default NULL,
  `password` char(32) DEFAULT NULL,
  `tmp` char(32) NOT NULL DEFAULT '0',
  `ip` varchar(16) DEFAULT NULL,
  `group` int(10) NOT NULL DEFAULT 1,
  `deadtry` tinyint(1) DEFAULT 0,

-- Статистические данные --

  `comments_num` int(10) NOT NULL,
  `gameplay_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `play_times` int(10) NOT NULL,
  `undress_times` int(10) NOT NULL,
  `default_skin` tinyint(1) NOT NULL DEFAULT '1',

-- Игровая сессия --
	
  `clientToken` varchar(255) default NULL
  `session` varchar(255) default NULL,
  `server` varchar(255) default NULL,  

  PRIMARY KEY (`id`),
  UNIQUE KEY `Login` (`login`),
  KEY `group_id` (`group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

-- Баны по IP для защиты от повторной регистрации

CREATE TABLE IF NOT EXISTS `ip_banning` (
  `IP` varchar(16) NOT NULL,
  `time_start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ban_until` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ban_type` tinyint(1) NOT NULL DEFAULT 1,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

CREATE TABLE IF NOT EXISTS `data` (
  `property` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  UNIQUE KEY `property` (`property`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `data` (`property`, `value`) VALUES
('latest-game-build', '10746'),
('launcher-version', '13'),
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