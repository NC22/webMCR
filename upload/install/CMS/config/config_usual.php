<?php 
$bd_names_PREFIX = 'mcr_'; /* Not used by default. Will add for usual table names if install with main CMS */
  
$bd_names = array (
	'users' 			=> 'accounts',
	'likes'				=> 'likes',
	'ip_banning' 		=> 'ip_banning',
	'news'				=> 'news',
	'news_categorys'	=> 'news_categorys',
	'groups'			=> 'groups',
	'data'				=> 'data',
	'files'				=> 'files',
	'comments'			=> 'comments', 
	'servers'			=> 'servers',
	'action_log'		=> 'action_log',
	'iconomy'			=> false,
);

$config = array (
/* MySQL connection */

	'db_host'		=> 'localhost',
	'db_port'		=> 3306,
	'db_login'		=> 'root',
	'db_passw'		=> '',
	'db_name'		=> 'mcraft',

/* site constants */
  
	's_name'		=> 'MCR '.MCR,
	's_about'		=> 'Личный кабинет для онлайн сервера игры Minecraft',
	's_keywords'	=> 'сервер игра онлайн NC22 Minecraft',
	's_dpage'		=> 'news', 
	's_theme'		=> View::def_theme,	
	's_root'		=> '/',
  
	'news_by_page'	=> 5,
	'comm_by_page'	=> 5,  
	'comm_revers'	=> false,
	'game_news'		=> 1,
  
/* system */

	'timezone'	=> 'Asia/Vladivostok',
	'sbuffer'	=> true,  
	'skinposer'	=> false, 
	'rewrite'	=> true,
	'log'		=> false,
	'offline'	=> false,
  
	'install' => true,
	'p_logic' => 'usual',
	
	'smtp'		=> false,
	
/* action limiter */

	'action_log'	=> false,	// log connect with BD times and detect some fast users, possible bots
	'action_max'	=> 10,		// maximum exec php script's times ( server monitorings, page refresh, profile edit and etc.)
	'action_time'	=> 1,		// per seconds. 
	'action_ban'	=> 60,		// ban time in seconds
);
  
$site_ways = array (
	'style'		=> 'style/',
	'mcraft'	=> 'MineCraft/',
	'skins'		=> 'MinecraftSkins/',
	'cloaks'	=> 'MinecraftCloaks/',
	'distrib'	=> 'MinecraftDownload/',
);

$bd_money = array ( /* iconomy or some other plugin, just check names */
  'login' => 'username',
  'money' => 'balance',
);

$bd_users = array (
  'login' => 'login',
  'id' => 'id',
  'password' => 'password',
  'ip' => 'ip',
  'email' => 'email',
  'female' => 'female',
  'group' => 'group',
  'deadtry' => 'deadtry',
  'tmp' => 'tmp',
  'session' => 'session',
  'server' => 'server',
  'clientToken'   => 'clientToken',
  'ctime' => 'create_time',
);