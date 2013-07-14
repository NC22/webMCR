<?php 
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

	'deadtry' 	=> 'mcr_deadtry',
	'female'	=> 'mcr_gender',  
	'group' 	=> 'mcr_group',
	'tmp' 		=> 'mcr_tmp',
	'session'	=> 'mcr_session',
	'action_log' => 'mcr_action_log',
	'server' 	=> 'mcr_server',
        'clientToken'   => 'mcr_clientToken'    
);

/* Exists IPB fields */

$bd_users['users'] 	= 'members';

$config['db_name'] 	= 'ipb';
$config['p_logic'] 	= 'ipb';
$config['p_sync'] 	= false;
$config['s_name'] 	= 'IPB patch';
 
$site_ways['main_cms'] = false; 