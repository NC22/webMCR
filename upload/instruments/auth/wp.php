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
    $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $count_log2 = strpos($itoa64, $data['pass_db'][3]);
    $count = 1 << $count_log2;
    $salt = substr($data['pass_db'], 4, 8);
    $input = md5($salt . $data['pass'], TRUE);
    do
    {
        $input = md5($input . $data['pass'], TRUE);
    }
    while (--$count);
               
    $output = substr($data['pass_db'], 0, 12);
               
    $count = 16;
    $i = 0;
    do
    {
        $value = ord($input[$i++]);
        $cryptPass .= $itoa64[$value & 0x3f];
        if ($i < $count)
            $value |= ord($input[$i]) << 8;
        $cryptPass .= $itoa64[($value >> 6) & 0x3f];
        if ($i++ >= $count)
            break;
        if ($i < $count)
            $value |= ord($input[$i]) << 16;
        $cryptPass .= $itoa64[($value >> 12) & 0x3f];
        if ($i++ >= $count)
            break;
        $cryptPass .= $itoa64[($value >> 18) & 0x3f];
    }
        while ($i < $count);
               
    $cryptPass = $output . $cryptPass;
 
    if ($data['pass_db'] == $cryptPass) return true;
	else return false;
}

}