<?php
require('../system.php');

if (empty($_GET['user']) or empty($_GET['serverId'])) {
  vtxtlog("[checkserver.php] checkserver process [GET parameter empty] [ ".((empty($_GET['user']))? 'LOGIN ':'').((empty($_GET['serverId']))? 'SERVERID ':'')."]");
  exit('NO');
}
	loadTool('user.class.php'); 
	BDConnect('checkserver');
	
	$user 		= $_GET['user']; 
	$serverid 	= $_GET['serverId'];

	if (!preg_match("/^[a-zA-Z0-9_-]+$/", $user)  or
	    !preg_match("/^[a-z0-9_-]+$/", $serverid)) {
		
		vtxtlog("[checkserver.php] error checkserver process [info login ".$user." serverid ".$serverid."]");
		exit('NO');				
	} 	
		
	$result = BD("SELECT `{$bd_users['login']}` FROM {$bd_names['users']} WHERE `{$bd_users['login']}`='".TextBase::SQLSafe($user)."' AND `{$bd_users['server']}`='".TextBase::SQLSafe($serverid)."'");

	if( mysql_num_rows($result) == 1 ){
		
	   $user_login = new User($user,$bd_users['login']);
	   $user_login->gameLoginConfirm();
	   vtxtlog("[checkserver.php] Server Test [Success]");
	   exit('YES'); 		   
	}		
	   
	vtxtlog("[checkserver.php] [User not found] User [$user] Server ID [$serverid]");
    exit('NO');
?>