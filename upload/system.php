<?php
// error_reporting(E_ALL);

define('MCR', 1);  
define('MCR_ROOT', dirname(__FILE__).'/');

if (!file_exists(MCR_ROOT.'config.php')) { header("Location: install/install.php"); exit; }

require(MCR_ROOT.'config.php');

define('MCRAFT', MCR_ROOT.$site_ways['mcraft']);
define('MCR_STYLE', './'.$site_ways['style']);

define('STYLE_URL', $site_ways['style']);
define('BASE_URL', $config['s_root']);

require(MCR_ROOT.'instruments/base.class.php');
require(MCR_ROOT.'instruments/auth/'.$config['p_logic'].'.php');

date_default_timezone_set($config['timezone']);

$user = false; $link = false;

function BD( $query ) {
global $link;
	
	$result = mysql_query( $query, $link ); 
	
	if (is_bool($result) and $result == false)  
	
	vtxtlog('SQLError: ['.$query.']');
	
	return $result;
}

function BDConnect() {
global $link, $config;

$link = mysql_connect($config['db_host'].':'.$config['db_port'], $config['db_login'], $config['db_passw']) or die("ОШИБКА MySQL Базы данных. Сервер не отвечает или не удается пройти авторизацию");
        mysql_select_db($config['db_name'], $link) or die("ОШИБКА MySQL Базы данных. Не найдена база данных с именем ".$config['db_name']);
	
	BD("SET time_zone = '".date('P')."'");
	BD("SET character_set_client='utf8'"); 
	BD("SET character_set_results='utf8'"); 
	BD("SET collation_connection='utf8_general_ci'"); 
}

/* Системные функции */

function tmp_name($folder, $pre = '', $ext = 'tmp'){
    $name  = $pre.time().'_';
	  
    for ($i=0;$i<8;$i++) $name .= chr(rand(97,121));
	  
    $name .= '.'.$ext;
	  
return (file_exists($folder.$name))? tmp_name($folder,$pre,$ext) : $name;
}

function ratio($file, $baze = 64, $prop = 2) {

	$input_size = @getimagesize($file);

	if (empty($input_size)) return false;

	if (round($input_size[0] / $input_size[1], 2) != round($prop,2)) return false;
	else if ($input_size[0] < $baze) return false;

	$mp = $input_size[0] / $baze;

return $mp;
}

function POSTGood($post_name, $format = array('png')) {

if ( empty($_FILES[$post_name]['tmp_name']) or 

     $_FILES[$post_name]['error'] != UPLOAD_ERR_OK or
	 
	 !is_uploaded_file($_FILES[$post_name]['tmp_name']) ) return false;
   
$extension = strtolower(substr($_FILES[$post_name]['name'], 1 + strrpos($_FILES[$post_name]['name'], ".")));

if (is_array($format) and !in_array($extension, $format)) return false;
   
return true;
}

function POSTSafeMove($post_name, $tmp_dir = false) {
	
	if (!POSTGood($post_name, false)) return false;
	
	if (!$tmp_dir) $tmp_dir = MCRAFT.'tmp/';

	if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0777); 

	$tmp_file = tmp_name($tmp_dir);
	if (!move_uploaded_file( $_FILES[$post_name]['tmp_name'], $tmp_dir.$tmp_file )) { 

	vtxtlog('[Ошибка модуля загрузки] Убедитесь, что папка "'.$tmp_dir.'" доступна для ЗАПИСИ.');
	return false;
	}

return array('tmp_name' => $tmp_file, 'name' => $_FILES[$post_name]['name'], 'size_mb' => round($_FILES[$post_name]['size'] / 1024 / 1024, 2));
}

function POSTUpload($post_name, $final_way, $baze = 64, $prop = 2) {
global $user;

if (empty($user) or $user->lvl() <= 0) return 1;

	$tmp_dir = MCRAFT.'tmp/';

	$tmp_file_info = POSTSafeMove($post_name, $tmp_dir);

if (!$tmp_file_info) return 1610;	

	$tmp_file = $tmp_dir.$tmp_file_info['tmp_name'];

$fsize = round($_FILES[$post_name]['size'] / 1024);
	
if ( (int) $user->getPermission('max_fsize') < $fsize ) 
	
	{ unlink($tmp_file); return 1601; }
  
$input_ratio = ratio($tmp_file, $baze, $prop);
		
if (!$input_ratio or $input_ratio > (int) $user->getPermission('max_ratio')) 	

    { unlink($tmp_file); return 1602; }
	
if (file_exists($final_way)) unlink($final_way);

if (rename( $tmp_file, $final_way )) chmod($final_way,0777);
else { 

unlink($final_way);

vtxtlog('[Ошибка модуля загрузки] Убедитесь, что папка "'.$tmp_dir.'" доступна для ЧТЕНИЯ.');  
return 1611; 
}

return 1;

}

function randString( $pass_len = 50 ) {
    $allchars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $string = "";
    
    mt_srand( (double) microtime() * 1000000 );
    
    for ( $i=0; $i<$pass_len; $i++ )
	$string .= $allchars{ mt_rand( 0, strlen( $allchars )-1 ) };
	
    return $string;
}

function sqlConfigGet($type){
global $bd_names;
	
    switch($type){
	case 'latest-game-build':
	case 'rcon-port':
	case 'rcon-serv':	
	case 'rcon-pass':
	case 'next-reg-time':
	case 'email-verification':
	case 'email-verification-salt':
	case 'launcher-version':  break;
	default : return false;
	}
	
    $result = BD("SELECT `value` FROM `{$bd_names['data']}` WHERE `property`='".TextBase::SQLSafe($type)."'");   

    if ( mysql_num_rows( $result ) != 1 ) return false;
	
	$line = mysql_fetch_array($result, MYSQL_NUM );
	
	return $line[0];		
}

function sqlConfigSet($type,$value) {
global $bd_names;

    switch($type){
	case 'latest-game-build':
	case 'rcon-port':
	case 'rcon-pass':
	case 'rcon-serv':
    case 'next-reg-time':	
	case 'email-verification':
	case 'email-verification-salt':
	case 'launcher-version': break;
	default : return false;
	}
	
    $result = BD("UPDATE `{$bd_names['data']}` SET `value`='".TextBase::SQLSafe($value)."' WHERE `property`='".TextBase::SQLSafe($type)."'"); 
	if ($result and !mysql_affected_rows()) {
	
		$result = BD("INSERT INTO `{$bd_names['data']}` (value,property) VALUES ('".TextBase::SQLSafe($value)."','".TextBase::SQLSafe($type)."')");
		if (mysql_affected_rows()) return true;
		
	} else return true;
}

function GetRealIp(){

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
	
	$ip = $_SERVER['HTTP_CLIENT_IP']; 
	 
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
	
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	 
	else 
	 
	$ip = $_SERVER['REMOTE_ADDR'];
 
return $ip; 
}

function RefreshBans() {
global $bd_names;

	BD("DELETE FROM {$bd_names['ip_banning']} WHERE (ban_until='0000-00-00 00:00:00') AND (time_start<NOW()-INTERVAL ".((int) sqlConfigGet('next-reg-time'))." HOUR)");
	BD("DELETE FROM {$bd_names['ip_banning']} WHERE (ban_until<>'0000-00-00 00:00:00') AND (ban_until<NOW())");					
}

function vtxtlog($string) {
global $config;

/* Если ajax - то выводить в message */

if (!$config['log']) return;

$log_file = MCR_ROOT.'log.txt';

	if (file_exists($log_file) and round(filesize ($log_file) / 1048576) >= 50) unlink($log_file);
	
	if ( !$fp = fopen($log_file,'a') ) exit('[system.php] Ошибка открытия файла '.$log_file.' убедитесь, что файл доступен для ЗАПИСИ');
	
	fwrite($fp,date("H:i:s d-m-Y").' < '.$string.PHP_EOL); 
	fclose($fp);	
}
?>