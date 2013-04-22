<?php 

$bd_users = array (

/* Valid XenForo fields */

	'login'		=> 'username',
	'id'		=> 'user_id',  
	'email'		=> 'email',
	'female'	=> 'gender',
	'ctime' 	=> 'register_date',
  
/* Required MCR fields */

  
  'password' 	=> 'mcr_default', 
  'ip' 			=> 'mcr_ip',  
  'group' 		=> 'mcr_group',
  'tmp' 		=> 'mcr_tmp',
  'session'		=> 'mcr_session',
  'server' 		=> 'mcr_server',
);

$bd_names = array (

/* Exists XenForo fields */

  'users' 		=> 'xf_user',
  'user_auth' 	=> 'xf_user_authenticate',
  
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

$config['db_name'] 	= 'xenforo';
$config['p_logic'] 	= 'xenforo';
$config['p_sync'] 	= true;
$config['s_name'] 	= 'xenForo patch';
 
$site_ways['main_cms'] = false; 