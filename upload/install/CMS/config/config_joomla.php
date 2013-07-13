<?php 
$bd_users = array (

/* Valid Joomla fields */

	'login'		=> 'username',
	'id'		=> 'id',  
	'email'		=> 'email',
	'ctime' 	=> 'registerDate',
	'password'	=> 'password',   
	
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

/* Common Joomla fields */

$bd_names['users'] 	= 'prefix_users';

$config['db_name'] 	= 'joomla';
$config['p_logic'] 	= 'joomla';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'Joomla patch';
 
$site_ways['main_cms'] = false; 