<?php 
  
$bd_names = array (

/* Exists IPB fields */

  'users' 		=> 'members',
  
  'files'			=> 'mcr_files',
  'ip_banning' 		=> 'mcr_ip_banning',
  'news'			=> 'mcr_news',
  'news_categorys' 	=> 'mcr_news_categorys',
  'groups' 			=> 'mcr_groups',
  'data' 			=> 'mcr_data',
  'comments' 		=> 'mcr_comments', 
  'servers' 		=> 'mcr_servers',
  'iconomy' 		=> false,
);

$bd_users = array (

/* Valid IPB fields */

	'login'		=> 'name',
	'id'		=> 'member_id',  
	'email'		=> 'email',
	'ctime' 	=> 'joined',
	'ip' 		=> 'ip_address',   
	'password' 	=> 'members_pass_hash', 	
	'salt_pwd' 	=> 'members_pass_salt', 	
	
/* Required MCR fields */

	'female'	=> 'mcr_gender',  
	'group' 	=> 'mcr_group',
	'tmp' 		=> 'mcr_tmp',
	'session'	=> 'mcr_session',
	'action_log'		=> 'mcr_action_log',
	'server' 	=> 'mcr_server',
);

$config['db_name'] 	= 'ipb';
$config['p_logic'] 	= 'ipb';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'IPB patch';
 
$site_ways['main_cms'] = false; 