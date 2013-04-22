<?php 
$bd_users = array (

/* Valid DLE fields */

	'login'		=> 'name',
	'id'		=> 'user_id',  
	'email'		=> 'email',

	'ctime' 	=> 'reg_date',
	'password' 	=> 'password',   
	
/* Required MCR fields */  

	'female'	=> 'mcr_gender',
	'ip' 		=> 'mcr_ip',  
	'group' 	=> 'mcr_group',
	'tmp' 		=> 'mcr_tmp',
	'session'	=> 'mcr_session',
	'server' 	=> 'mcr_server',
);

$bd_names['users'] 	= 'dle_users';

$config['db_name'] 	= 'dle';
$config['p_logic'] 	= 'dle';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'DLE patch';
 
$site_ways['main_cms'] = false; 