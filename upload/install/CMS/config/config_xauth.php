<?php 
$bd_users = array (

/* Valid xAuth fields */

	'login'		=> 'playername',
	'id'		=> 'id',  
	'email'		=> 'email',
	'ctime' 	=> 'registerdate',
	'password' 	=> 'password',   
  
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

/* Common xAuth fields */

$bd_names['users'] 	= 'account';

$config['db_name'] 	= 'xauth';
$config['p_logic'] 	= 'xauth';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'xAuth patch';
 
$site_ways['main_cms'] = false; 