<?php 
$bd_users = array (

/* Valid xAuth fields */

	'login'		=> 'playername',
	'id'		=> 'id',  
	'email'		=> 'email',

	'ctime' 	=> 'registerdate',
	'password' 	=> 'password',   
  
/* Required MCR fields */
  
	'female'	=> 'mcr_gender',
	'ip' 		=> 'mcr_ip',  
	'group' 	=> 'mcr_group',
	'tmp' 		=> 'mcr_tmp',
	'session'	=> 'mcr_session',
	'server' 	=> 'mcr_server',
);

$bd_names = array (

/* Exists xAuth fields */

  'users' 		=> 'account',
  
  'files'			=> 'mcr_files',
  'ip_banning' 		=> 'mcr_ip_banning',
  'news'			=> 'mcr_news',
  'news_categorys' 	=> 'mcr_news_categorys',
  'groups' 			=> 'mcr_groups',
  'data' 			=> 'mcr_data',
  'comments' 		=> 'mcr_comments', 
  'servers' 		=> 'mcr_servers',
  'action_log'		=> 'mcr_action_log',
  'iconomy' 		=> false,
);

$config['db_name'] 	= 'xauth';
$config['p_logic'] 	= 'xauth';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'xAuth patch';
 
$site_ways['main_cms'] = false; 