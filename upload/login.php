<?php
if (empty($_GET['out']) and empty($_POST['login'])) exit;

require('./system.php');
require(MCR_ROOT.'instruments/ajax.php');
require(MCR_ROOT.'instruments/user.class.php');

BDConnect('login');

if (isset($_GET['out'])) {

	header("Location: ".BASE_URL);	
	MCRAuth::userLoad();  	
	if (!empty($user)) $user->logout();	
	
} elseif (isset($_POST['login'])) {

	 $name = $_POST['login']; $pass = $_POST['pass'];   
	 $tmp_user = new User($name, (strpos($name, '@') === false)? $bd_users['login'] : $bd_users['email']); 
	 
	if (!$tmp_user->id()) 
	
		aExit(4, 'Пользователь с таким именем или e-mail\'ом не существует.'); 
		
	if (!$tmp_user->authenticate($pass)) 
	
		aExit(1, 'Неверный пароль.<br /> <a href="#" style="color: #656565;" onclick="RestoreStart(); return false;">Восстановить пароль ?</a>'); 

	if ($tmp_user->lvl() <= 0) aExit(4, 'Ваш аккаунт заблокирован.');	
	
	$tmp_user->login(randString( 15 ), GetRealIp(), (!empty($_POST['save']))? true : false);
	$user = $tmp_user;
	aExit(0, 'success');	  
}
?>