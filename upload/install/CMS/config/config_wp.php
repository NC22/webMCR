<?php 
$bd_users = array (

/* Valid WP fields */

	'login'		=> 'user_login',
	'id'		=> 'ID',  
	'email'		=> 'user_email',
	'ctime' 	=> 'user_registered',
	'password' 	=> 'user_pass',   
  
/* Required MCR fields */
 
	'deadtry' 	=> 'mcr_deadtry',
	'female'	=> 'mcr_gender',
	'ip' 		=> 'mcr_ip',  
	'group' 	=> 'mcr_group',
	'tmp' 		=> 'mcr_tmp',
	'session'	=> 'mcr_session',
	'server' 	=> 'mcr_server',
        'clientToken'   => 'mcr_clientToken'
);

/* Common WP fields */

$bd_names['users'] 	= 'wp_users';

$config['db_name'] 	= 'wp';
$config['p_logic'] 	= 'wp';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'WordPress patch';
 
$site_ways['main_cms'] = false; 