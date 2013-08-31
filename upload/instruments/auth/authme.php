<?php
if (!defined('MCR')) exit;

require(MCR_ROOT.'instruments/auth/usual.php');

Class MCMSAuth {

/* Загрузка API главной CMS */

public static function start() { return; } 

/* Проверка авторизации пользователя в главной CMS */

public static function userLoad() { return; } 

/* Авторизация пользователя в главной CMS */

public static function login($id) { return; } 

/* Выход пользователя в главной CMS */

public static function logout() { return; }

/* Проверка авторизации пользователя в webMCR */

public static function userInit() { 
MCRAuth::LoadSession();
}

/* Генерация пароля */

public static function createPass($password) { 	

	$salt = substr(hash('sha256', uniqid(rand())), 0, 16);
	$hash = hash('sha256', hash('sha256', $password . $salt));
	$rpass = ('$SHA$'.$salt.'$'.hash('sha256',hash('sha256',$password).$salt));

	return $rpass;    
}

/* 
	Проверка пароля при авторизации 
	data [4]
	'pass_db'	 - зашифрованный пароль из БД, 
	'pass'		 - введенный пароль
	'user_id'	 - идентификатор пользователя
	'user_name'	 - логин пользователя
*/

public static function checkPass($data) {

	$tmp = explode('$', $data['pass_db']);
	$result = false;
	if(hash('sha256', hash('sha256', $data['pass']) . $tmp[2]) == $tmp[3])
	
		$result = true;
	
	return $result;
}

}