<?php
header('Content-Type: text/html; charset=UTF-8');

error_reporting(E_ALL); 

define('MCR_ROOT', dirname(dirname(__FILE__)).'/');

$mode = (!empty($_POST['mode']) or !empty($_GET['mode']))? ((empty($_GET['mode']))? $_POST['mode'] : $_GET['mode'] ): $mode = 'usual';
$step = (!empty($_POST['step']))? (int) $_POST['step'] : $step = 1;

switch ($mode) { /* Допустимые идентификаторы CMS */
	case 'xenforo':		$main_cms = 'xenForo';				break; /* [+] */
	case 'ipb':			$main_cms = 'Invision Power Board';	break; /* [+] */
	case 'dle':			$main_cms = 'DataLife Engine';		break; /* [+] */
	case 'wp':			$main_cms = 'WordPress';			break; /* [+] */	
	case 'joomla':		$main_cms = 'Joomla!';				break; /* [+] */
	case 'xauth':		$main_cms = 'xAuth';				break; /* [+] */
	case 'authme':		$main_cms = 'AuthMe';				break; /* [+] */
	default :			$main_cms = false; $mode = 'usual';	break;
} 	

define('BASE_URL', Root_url());

require_once(MCR_ROOT.'instruments/base.class.php');
require_once(MCR_ROOT.'instruments/alist.class.php');

if (file_exists(MCR_ROOT.'config.php')) {
include MCR_ROOT . 'config.php';

if (!$config['install']) { header('Location: '.BASE_URL); exit; } 
elseif ($config['p_logic'] != $mode) /* Установка была не завершена, файл существует и режим установки не совпадает с выбранным - удаляем */

	if (unlink(MCR_ROOT.'config.php')) { header('Location: '.BASE_URL.'install/install.php?mode='.$mode); exit; }  
	else { echo 'Файл '.MCR_ROOT.'config.php уже существует. Удалите его, для продолжения установки.'; exit; } 

} else { 

include './CMS/config/config_usual.php';

	if ($mode != 'usual') {
		
		foreach ($bd_names as $key => $value)
			
			if ($value) $bd_names[$key] = $bd_names_PREFIX . $value ;
		
		include './CMS/config/config_'.$mode.'.php';
	}
}

define('MCR_STYLE', MCR_ROOT.$site_ways['style']);

include MCR_ROOT . 'instruments/timezones.php';

define('STYLE_URL', $site_ways['style']);
define('DEF_STYLE_URL', STYLE_URL . View::def_theme . '/');
define('CUR_STYLE_URL', DEF_STYLE_URL);

$page = 'Настройка '.PROGNAME;
$save_conf_err = 'Ошибка создания \ перезаписи файла '.MCR_ROOT.'config.php (корневая дирректория сайта). Папка защищена от записи \ файл не доступен для записи. Настройки не были сохранены.';

$i_sd = 'other/install/';

$content_advice = 'Заполните форму для завершения установки '.PROGNAME;
$content_servers = ''; $content_js = '';
$content_side = View::ShowStaticPage('install_side.html', $i_sd);

$addition_events = '';
$info = '';  $cErr = '';
$info_color = 'alert-error'; //alert-success

$menu = new Menu('', false);
$menu->AddItem($page, BASE_URL.'install/install.php', true); 

$create_ways = array("skins", "cloaks", "distrib");
$content_menu = $menu->Show();

function checkBaseRequire() {
global $cErr, $site_ways, $create_ways;	
	
$p = '<p>'; $pe = '</p>';
	
if ( !extension_loaded('gd') ) 
	
	$cErr  .= $p.'Библиотека GD не подключена ( отображение скина \ плаща в профиле )'.$pe;

if ( ini_get('register_globals')  ) 
	
	$cErr .= $p.'Файл php.ini настроек PHP [Опция] <b>register_globals = On</b>. Привести в значение <b>Off</b>'.$pe;

if ( !function_exists('fsockopen')) 
	
	$cErr .= $p.'Функция fsockopen недоступна ( проверка состояния сервера )'.$pe;

if ( !function_exists('json_encode')) 
	
	$cErr .= $p.'Функция json_encode недоступна ( авторизация на сайте )'.$pe;

	checkRWOut(MCR_ROOT.'instruments/menu_items.php');
	// checkRWOut(MCR_ROOT.'config.php');
	
	$mcraft_dir = MCR_ROOT.$site_ways['mcraft'];
	
	checkRWOut(MCR_ROOT.$site_ways['mcraft']);
	checkRWOut($mcraft_dir.'tmp/skin_buffer/');
	checkRWOut($mcraft_dir.'userdata/');
	
    foreach ($site_ways as $key => $value)
	
		if ($value and in_array($key, $create_ways)) 
		
				checkRWOut($mcraft_dir.$value);	
}

function checkRWOut($fname, $create = false) {
global $cErr;

	$is_dir = substr_count($fname, '.');

	if (!checkRW($fname, $create)) 
	
		$cErr .= '<p>'.($is_dir ? 'Файл' : 'Папка').' '.$fname.' . Нет доступа для чтения \ записи </p>';
}

function checkRW($filename, $create = false) {

	if (!substr_count($filename, '.')) // is dir
		
		if (@file_exists($filename)) return true;
		else return false;
		
	if ($create) {
	
			$file = fopen($filename, 'w');
			
		if ($file) fclose($filename);
		else return false;
		
		if (is_readable($filename)) return true;	
	}
	
	if (is_readable($filename) and is_writable($filename)) return true;

	return false;
}

function createWays(){
global $site_ways, $create_ways;	
						
    foreach ($site_ways as $key => $value)
		if ($value and in_array($key, $create_ways) and !is_dir(MCR_ROOT.$site_ways['mcraft'].$value)) 
				mkdir(MCR_ROOT.$site_ways['mcraft'].$value, 0777, true);
}

function findCMS($way){
global $main_cms, $mode, $info;

	if ( !TextBase::StringLen($way)) { 
		$info = 'Укажите путь до папки '.$main_cms.'.';
		return false;
	}
		
	switch ($mode) {
		case 'xenforo': $file = 'library/XenForo/Autoloader.php'; break; 
		case 'ipb':		$file = 'admin/sources/base/ipsController.php';	break; 
		default: return false; break;
	} 
	
	if (!file_exists($way.$file)) 
	$info = 'Путь до папки '.$main_cms.' указан неверно. В папке отсутствует поддериктория с файлом '.$file;	
	else return true;
	
	return false;
}

function BD( $query ) {
    return mysql_query( $query );
}

function BD_ColumnExist($table, $column) {
	return (@mysql_query("SELECT `$column` FROM `$table` LIMIT 0, 1"))? true : false;
}

function Root_url(){
	$root_url = str_replace('\\', '/', $_SERVER['PHP_SELF']); 
	$root_url = explode("install/install.php", $root_url, -1);
	if (sizeof($root_url)) return $root_url[0];
	else return '/';
}

function Mode_rewrite(){
	
	if (function_exists('apache_get_modules')) {
	  
	  $modules = apache_get_modules();
	  return in_array('mod_rewrite', $modules);
	  
	} else return getenv('HTTP_MOD_REWRITE')=='On' ? true : false ;	
	return false;
}

function DBConnect() {
global $link,$config;

$link = mysql_connect($config['db_host'].':'.$config['db_port'], $config['db_login'],  $config['db_passw'] );
if (!$link) return 1;
if (!mysql_select_db($config['db_name'],$link)) return 2;

BD("set character_set_client = 'utf8'"); 
BD("set character_set_results = 'utf8'"); 
BD("set collation_connection = 'utf8_general_ci'");  

return false;
}

function ConfigPostStr($postKey){
 return (isset( $_POST[$postKey]))? TextBase::HTMLDestruct($_POST[$postKey]) : '';
}

function ConfigPostInt($postKey){
 return (isset( $_POST[$postKey]))? (int)$_POST[$postKey] : 0;
}

function CreateAdmin($site_user) {
global $config,$bd_names,$bd_users,$info;

	$site_password   = ConfigPostStr('site_password');
	$site_repassword = ConfigPostStr('site_repassword');
	$result = false;
	
		if ( !TextBase::StringLen($site_password)	) $info = 'Введите пароль.';
	elseif ( !TextBase::StringLen($site_repassword)	) $info = 'Введите повтор пароля.';
	elseif ( strcmp($site_password,$site_repassword)) $info = 'Пароли не совпадают.';
	else {

		$result = BD("SELECT `{$bd_users['login']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['login']}`='$site_user'");			  
		if ( mysql_num_rows($result) ) BD("DELETE FROM `{$bd_names['users']}` WHERE `{$bd_users['login']}`='$site_user'");
					
		require_once(MCR_ROOT.'instruments/auth/'.$config['p_logic'].'.php');
				
		BD("INSERT INTO `{$bd_names['users']}` (`{$bd_users['login']}`,`{$bd_users['password']}`,`{$bd_users['ip']}`,`{$bd_users['group']}`) VALUES('$site_user','".MCRAuth::createPass($site_password)."','127.0.0.1',3)");	
		$result = true;
	}

return $result;
}

if (isset($_POST['step']))

switch ($step) {
	case 1:     
	$mysql_port     = ConfigPostInt('mysql_port')		;
	$mysql_adress   = ConfigPostStr('mysql_adress')		;
	$mysql_bd       = ConfigPostStr('mysql_bd')			;
	$mysql_user     = ConfigPostStr('mysql_user')		;
	$mysql_password = ConfigPostStr('mysql_password')	;
	$mysql_rewrite  = (empty($_POST['mysql_rewrite']))? false : true;
	
		if ( !$mysql_port ) $info = 'Укажите порт для подключения к БД.';
	elseif ( !TextBase::StringLen($mysql_adress) ) $info = 'Укажите адресс сервера MySQL.';
	elseif ( !TextBase::StringLen($mysql_user) )   $info = 'Укажите пользователя для подключения к MySQL серверу.';
	else {
		
		$config['db_host']  = $mysql_adress   ; 
		$config['db_port']  = $mysql_port     ;
		$config['db_name']  = $mysql_bd       ; 
		$config['db_login'] = $mysql_user     ;
		$config['db_passw'] = $mysql_password ;
		
				$connect_result = DBConnect();	
			if ($connect_result == 1) $info = 'Данные для подключения к БД не верны. Возможно не правильно указан логин и пароль.';
		elseif ($connect_result == 2) $info = 'Не найдена база данных с именем '.$mysql_bd;
		else {
		    
			$config['rewrite'] = Mode_rewrite();
			$config['s_root']  = Root_url();
			
			if (MainConfig::SaveOptions()) $step = 2; 
			else $info = $save_conf_err;	

			
			include './CMS/sql/sql_common.php';				
			if (!$main_cms) include './CMS/sql/sql_usual.php';	
			
			include './18_fix.php';
		}		
	}	
	break;
	case 2:
	$site_user = ConfigPostStr('site_user');
	$mysql_rewrite  = (empty($_POST['mysql_rewrite']))? false : true;
	
	if ( !TextBase::StringLen($site_user)) {
	
		$info = 'Укажите имя пользователя.';
		break;
	}
	
		$connect_result = DBConnect();		
	if ($connect_result) { $info = 'Ошибка настройки соединения с БД.'; break; }	
	
	if ($main_cms) {
		
		$bd_names['users']  = ConfigPostStr('bd_accounts_mcms');		
		$bd_alter_users = "ALTER TABLE `{$bd_names['users']}` ";
		
		include './CMS/sql/sql_'.$mode.'.php';	
		
		if ( !TextBase::StringLen($bd_names['users'])) { $info = 'Введите название таблицы пользователей.'; break; }
		
		$config['p_sync']  = (empty($_POST['session_sync']))? false : true;
		
		$result = BD("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['login']}`='$site_user'");
		
		if (is_bool($result) and $result == false) { $info = 'Название таблицы пользователей указано неверно.'; break; }		
		if (!mysql_num_rows( $result )) { $info = 'Пользователь с таким именем не найден.'; break; }		
		
		$line = mysql_fetch_array($result, MYSQL_NUM);		
		
		if ($mode == 'xenforo') {
		
			$cms_way = (isset( $_POST['main_cms']))? $_POST['main_cms'] : '';			
			if (!findCMS($cms_way)) break;
			
			$site_ways['main_cms'] = $cms_way;			
			$bd_names['user_auth']  = ConfigPostStr('bd_auth_xenforo');
			
			$result = BD("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['id']}`='".$line[0]."'");		
			if (is_bool($result) and $result == false) { $info = 'Название таблицы c дополнительными данными указано неверно.'; break; }
		}
			
		if ($mode == 'xauth' and !CreateAdmin($site_user)) break;		
			
		if ($mode != 'xauth') BD("UPDATE `{$bd_names['users']}` SET `{$bd_users['group']}`='3' WHERE `{$bd_users['login']}`='$site_user'");	
		$step = 3; 	
		MainConfig::SaveOptions();			
		
	} else if (CreateAdmin($site_user)) $step = 3; 		
	break;
	case 3:
	$site_name   = ConfigPostStr('site_name' );
	$site_about  = ConfigPostStr('site_about');
	$keywords    = ConfigPostStr('site_keyword');	
	$timezone    = IsValidTimeZone(ConfigPostStr('site_timezone'));
	
	$sbuffer     = (!empty($_POST['sbuffer']))? true : false;	

	if ( TextBase::StringLen($keywords) > 200 ) $info = 'Ключевые слова занимают больше 200 символов ('.TextBase::StringLen($keywords).').';
	elseif (!$timezone)  $info = 'Выберите часовой пояс.';
    else {
	
	$config['s_name']		= $site_name 	;
	$config['s_about']		= $site_about 	; 	
	$config['s_keywords']	= $keywords   	;	
	$config['sbuffer']		= $sbuffer    	;	
	$config['timezone']		= $timezone		;

	$config['install']    = false; 

	if (MainConfig::SaveOptions()) $step = 4; 
	else $info = $save_conf_err;	
	
	}
	break;
}
	
createWays();	
checkBaseRequire();

ob_start(); 

if ($info) include View::Get('info.html', $i_sd); 
if ($cErr) {
	$info = $cErr;
	$info_color = 'alert-error';
	include View::Get('info.html', $i_sd); 
}

switch ($step) {
	case 1: 
	include View::Get('install_method.html', $i_sd);  
	include View::Get('install.html', $i_sd); 	
	break;
	case 2: 
	switch ($mode) {
		case 'usual': include View::Get('install_user.html', $i_sd); break;
		case 'xenforo': 
		case 'xauth': include View::Get('install_'.$mode.'.html', $i_sd); break;
		case 'authme': include View::Get('install_xauth.html', $i_sd); break;
		case 'ipb': 
		case 'joomla':		
		case 'dle':
		case 'wp': include View::Get('install_mcms.html', $i_sd); break;
	} 	
	break;
	case 3: include View::Get('install_constants.html', $i_sd); break;
	default: include View::Get('other.html', $i_sd); break;
}

$content_main = ob_get_clean();

include View::Get('index.html');
?>