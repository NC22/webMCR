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

	$salt = substr(hash('whirlpool', uniqid(rand(), true)), 0, 12);	
	$hash = hash('whirlpool', $salt . $password);
	$saltPos = (strlen($password) >= strlen($hash)) ? strlen($hash) : strlen($password);
	
return substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);     
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


	$saltPos = (strlen($data['pass']) >= strlen($data['pass_db'])) ? strlen($data['pass_db']) : strlen($data['pass']);
    $salt = substr($data['pass_db'], $saltPos, 12);
    $hash = hash('whirlpool', $salt . $data['pass']);
 
    $result = ($data['pass_db'] == substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos))? true : false;

return $result; 
}

}