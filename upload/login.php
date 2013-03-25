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
	exit;	
	
} elseif (isset($_POST['login'])) {

	 $name = $_POST['login']; $pass = $_POST['pass'];   

	 $result = BD("SELECT `{$bd_users['password']}`,`{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['login']}`='".TextBase::SQLSafe($name)."' OR `{$bd_users['email']}`='".TextBase::SQLSafe($name)."'"); 

	 if ( !$result or !mysql_num_rows( $result ) ) { mysql_close( $link ); aExit(4,'Пользователь с таким именем или e-mail\'ом не существует.'); } 
	 
	 $line = mysql_fetch_array( $result, MYSQL_NUM);
	  
	 if ( !MCRAuth::checkPass(array( 'pass_db' => $line[0], 'pass' => $pass, 'user_id' => $line[1], 'user_name' => $name )) ) {  
	 
	 mysql_close( $link ); 
	 aExit(1,'Неверный пароль.<br /> <a href="#" style="color: #656565;" onclick="RestoreStart(); return false;">Восстановить пароль ?</a>'); 
	 }
	 
	 $user = new User($line[1], $bd_users['id']);
	 
	 if ($user->lvl() <= 0) {
	 
		unset($user);
		mysql_close( $link );
		aExit(4,'Ваш аккаунт заблокирован.');	
	}
	
	$user->login(randString( 15 ), GetRealIp(), (!empty($_POST['save']))? true : false);	  
	aExit(0);	  
}
?>