<?php 
$bd_users = array (

/* Valid XenForo fields */

	'login'		=> 'username',
	'id'		=> 'user_id',  
	'email'		=> 'email',
	'female'	=> 'gender',
	'ctime' 	=> 'register_date',
  
/* Required MCR fields */

  'deadtry' 	=> 'mcr_deadtry',  
  'password' 	=> 'mcr_default', 
  'ip' 			=> 'mcr_ip',  
  'group' 		=> 'mcr_group',
  'tmp' 		=> 'mcr_tmp',
  'session'		=> 'mcr_session',
  'server' 		=> 'mcr_server',
  'clientToken'   => 'mcr_clientToken'
);

/* Exists XenForo fields */

$bd_names['users'] 	= 'xf_user';
$bd_names['user_auth'] 	= 'xf_user_authenticate';

$config['db_name'] 	= 'xenforo';
$config['p_logic'] 	= 'xenforo';
$config['p_sync'] 	= true;
$config['s_name'] 	= 'xenForo patch';
 
$site_ways['main_cms'] = false;