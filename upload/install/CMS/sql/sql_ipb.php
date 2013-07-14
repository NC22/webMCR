<?php 
if ($mysql_rewrite) {

BD("ALTER TABLE `{$bd_names['users']}` 
DROP `{$bd_users['session']}`,
DROP `{$bd_users['clientToken']}`,    
DROP `{$bd_users['server']}`,
DROP `{$bd_users['tmp']}`,
DROP `{$bd_users['female']}`,
DROP `{$bd_users['group']}`,
DROP `comments_num`,
DROP `gameplay_last`,
DROP `active_last`,
DROP `play_times`,
DROP `undress_times`,
DROP `default_skin`;");						 
}

BD($bd_alter_users."ADD `{$bd_users['deadtry']}` tinyint(1) DEFAULT 0;");
BD($bd_alter_users."ADD `{$bd_users['session']}` varchar(255) DEFAULT NULL;");
BD($bd_alter_users."ADD `{$bd_users['clientToken']}` varchar(255) DEFAULT NULL;");
BD($bd_alter_users."ADD `{$bd_users['server']}` varchar(255) DEFAULT NULL;");
BD($bd_alter_users."ADD `{$bd_users['tmp']}` char(32) NOT NULL DEFAULT '0';");
BD($bd_alter_users."ADD `{$bd_users['female']}` tinyint(1) NOT NULL DEFAULT '0';");
BD($bd_alter_users."ADD `{$bd_users['group']}` int(10) NOT NULL DEFAULT 1;");
BD($bd_alter_users."ADD `comments_num` int(10) NOT NULL DEFAULT 0;");
BD($bd_alter_users."ADD `gameplay_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';");
BD($bd_alter_users."ADD `active_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';");
BD($bd_alter_users."ADD `play_times` int(10) NOT NULL DEFAULT 0;");
BD($bd_alter_users."ADD `undress_times` int(10) NOT NULL DEFAULT 0;");
BD($bd_alter_users."ADD `default_skin` tinyint(1) NOT NULL DEFAULT '1';");

BD("INSERT INTO `{$bd_names['groups']}` 
(`id`,`name`,`lvl`,`system`,`change_skin`,`change_pass`,`change_login`,`change_cloak`,`add_news`,`add_comm`,`adm_comm`) VALUES 
(1,'Пользователь',2,1,1,0,0,0,0,1,0), 
(2,'Заблокированный',0,1,0,0,0,0,0,0,0), 
(3,'Администратор',15,1,1,0,1,1,1,1,1), 
(4,'Непроверенный',1,1,0,0,0,0,0,0,0), 
(5,'VIP Игрок',5,0,1,0,0,1,0,1,0);");	
?>