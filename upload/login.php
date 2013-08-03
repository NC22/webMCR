<?php
if (empty($_GET['out']) and empty($_POST['login'])) exit;

require('./system.php');

loadTool('ajax.php');
loadTool('user.class.php');

BDConnect('login');

if (isset($_GET['out'])) {

	header("Location: ".BASE_URL);	
	MCRAuth::userLoad();  	
	if (!empty($user)) $user->logout();	
	
} elseif (isset($_POST['login'])) {

	 $name = $_POST['login']; $pass = $_POST['pass'];   
	 $tmp_user = new User($name, (strpos($name, '@') === false)? $bd_users['login'] : $bd_users['email']); 
	 $ajax_message['auth_fail_num'] = (int)$tmp_user->auth_fail_num();
	 
	if (!$tmp_user->id()) 
	
		aExit(4, lng('AUTH_NOT_EXIST')); 

	if ($tmp_user->auth_fail_num() >= 5) CaptchaCheck(6);
	
	if (!$tmp_user->authenticate($pass)) { 
	
		$ajax_message['auth_fail_num'] = (int)$tmp_user->auth_fail_num();
		aExit(1, lng('AUTH_FAIL').'.<br /> <a href="#" style="color: #656565;" onclick="RestoreStart(); return false;">'.lng('AUTH_RESTORE').' ?</a>'); 
	}
	
	if ($tmp_user->lvl() <= 0) aExit(4, lng('USER_BANNED'));	
	
	$tmp_user->login(randString( 15 ), GetRealIp(), (!empty($_POST['save']))? true : false);
	aExit(0, 'success');	  
}
?>