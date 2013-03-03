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
	return 0;
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
	
    $cryptPass = false;
	$parts = explode( ':', $data['pass_db']);
	$salt = $parts[1];
	$cryptPass = md5($data['pass'] . $salt) . ":" . $salt;
	
	if ($data['pass_db'] == $cryptPass) return true;
	else return false;
}

}
