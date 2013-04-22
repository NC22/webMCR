<?php 
$bd_users = array (

/* Valid WP fields */

	'login'		=> 'user_login',
	'id'		=> 'ID',  
	'email'		=> 'user_email',

	'ctime' 	=> 'user_registered',
	'password' 	=> 'user_pass',   
  
/* Required MCR fields */
 
	'female'	=> 'mcr_gender',
	'ip' 		=> 'mcr_ip',  
	'group' 	=> 'mcr_group',
	'tmp' 		=> 'mcr_tmp',
	'session'	=> 'mcr_session',
	'server' 	=> 'mcr_server',
);

$bd_names = array (

/* Exists WP fields */

  'users' 		=> 'wp_users',

  'likes'			=> 'mcr_likes',   
  'files'			=> 'mcr_files',
  'ip_banning' 		=> 'mcr_ip_banning',
  'news'			=> 'mcr_news',
  'news_categorys' 	=> 'mcr_news_categorys',
  'groups' 			=> 'mcr_groups',
  'data' 			=> 'mcr_data',
  'comments' 		=> 'mcr_comments', 
  'action_log'		=> 'mcr_action_log',
  'servers' 		=> 'mcr_servers',
  'iconomy' 		=> false,
);

$config['db_name'] 	= 'wp';
$config['p_logic'] 	= 'wp';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'WordPress patch';
 
$site_ways['main_cms'] = false; 