<?php 
$bd_users = array (

/* Valid AuthMe fields */

	'login'		=> 'username',
	'id'		=> 'id',  
	'password' 	=> 'password',   
	'ip' 		=> 'ip', 
	
/* Required MCR fields */

	'deadtry' 	=> 'mcr_deadtry',
	'email'		=> 'mcr_email',  	
	'female'	=> 'mcr_gender',
 	'ctime' 	=> 'mcr_regtime', 
	'group' 	=> 'mcr_group',
	'tmp' 		=> 'mcr_tmp',
	'session'	=> 'mcr_session',
	'server' 	=> 'mcr_server',
        'clientToken'   => 'mcr_clientToken'
);

/* Common AuthMe fields */

$bd_names['users'] 	= 'authme';

$config['db_name'] 	= 'authme';
$config['p_logic'] 	= 'authme';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'AuthMe patch';
 
$site_ways['main_cms'] = false; 