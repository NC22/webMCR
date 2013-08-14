<?php
error_reporting(E_ALL);

$user = false; $link = false; $mcr_tools = array();

define('MCR_ROOT', dirname(__FILE__).'/');
define('MCR_LANG', 'ru_RU');

loadTool('base.class.php');

if (!file_exists(MCR_ROOT.'config.php')) { header("Location: install/install.php"); exit; }

require(MCR_ROOT.'instruments/locale/'.MCR_LANG.'.php');
require(MCR_ROOT.'config.php');

require(MCR_ROOT.'instruments/auth/'.$config['p_logic'].'.php');

define('MCRAFT', MCR_ROOT.$site_ways['mcraft']);
define('MCR_STYLE', MCR_ROOT.$site_ways['style']); 

define('STYLE_URL', $site_ways['style']); // deprecated
define('DEF_STYLE_URL', STYLE_URL . View::def_theme . '/');
// CUR_STYLE_URL - deleted; logic moved in base.class.php->View::GetURL 

define('BASE_URL', $config['s_root']);

date_default_timezone_set($config['timezone']);

function BD( $query ) {
global $link;
	
	$result = mysql_query( $query, $link ); 
	
	if (is_bool($result) and $result == false)  
	
	vtxtlog('SQLError: ['.$query.']');
	
	return $result;
}

function BDConnect($log_script = 'default') {
global $link, $config;

$link = mysql_connect($config['db_host'].':'.$config['db_port'], $config['db_login'], $config['db_passw']) or die(lng('BD_ERROR').lng('BD_AUTH_FAIL'));
        mysql_select_db($config['db_name'], $link) or die(lng('BD_ERROR').'. '.lng('BD_NOT_EXIST').' ('.$config['db_name'].')');
	
	BD("SET time_zone = '".date('P')."'");
	BD("SET character_set_client='utf8'"); 
	BD("SET character_set_results='utf8'"); 
	BD("SET collation_connection='utf8_general_ci'"); 
	
	if ($log_script and $config['action_log']) ActionLog($log_script);	
	CanAccess(2);	
}

/* Системные функции */

function loadTool( $name, $sub_dir = '') {
global $mcr_tools; 

	if (in_array($name, $mcr_tools)) return;
	
	$mcr_tools[] = $name;
	
	require( MCR_ROOT . 'instruments/' . $sub_dir . $name);	
}

function lng($key, $lang = false) {
global $MCR_LANG;

	return isset($MCR_LANG[$key]) ? $MCR_LANG[$key] : $key;
}

function tmp_name($folder, $pre = '', $ext = 'tmp'){
    $name  = $pre.time().'_';
	  
    for ($i=0;$i<8;$i++) $name .= chr(rand(97,121));
	  
    $name .= '.'.$ext;
	  
return (file_exists($folder.$name))? tmp_name($folder,$pre,$ext) : $name;
}

function InputGet($key, $method = 'POST', $type = 'str') {
	
	$blank_result = array( 'str' => '', 'int' => 0, 'float' => 0, 'bool' => false);
	
	if (($method == 'POST' and !isset($_POST[$key])) or
		($method != 'POST' and !isset($_GET[$key]))) return $blank_result[$type];
	
	$var = ($method == 'POST')? $_POST[$key] : $_GET[$key];
	
    switch($type){
		case 'str': return TextBase::HTMLDestruct($var); break;
		case 'int': return (int) $var; break;
		case 'float': return (float) $var; break;
		case 'bool': return (bool) $var; break;
	}	
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

	vtxtlog('[POSTSafeMove] --> "'.$tmp_dir.'" <-- '.lng('WRITE_FAIL'));
	return false;
	}

return array('tmp_name' => $tmp_file, 'name' => $_FILES[$post_name]['name'], 'size_mb' => round($_FILES[$post_name]['size'] / 1024 / 1024, 2));
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
	
	if (!in_array($type, ItemType::$SQLConfigVar)) return false;
	
    $result = BD("SELECT `value` FROM `{$bd_names['data']}` WHERE `property`='".TextBase::SQLSafe($type)."'");   

    if ( mysql_num_rows( $result ) != 1 ) return false;
	
	$line = mysql_fetch_array($result, MYSQL_NUM );
	
	return $line[0];		
}

function sqlConfigSet($type, $value) {
global $bd_names;

	if (!in_array($type, ItemType::$SQLConfigVar)) return false;
	
	$result = BD("INSERT INTO `{$bd_names['data']}` (value,property) VALUES ('".TextBase::SQLSafe($value)."','".TextBase::SQLSafe($type)."') ON DUPLICATE KEY UPDATE `value`='".TextBase::SQLSafe($value)."'");
	return true;
}

function GetRealIp(){

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
	
	$ip = $_SERVER['HTTP_CLIENT_IP']; 
	 
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
	
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	 
	else 
	 
	$ip = $_SERVER['REMOTE_ADDR'];
 
return substr($ip, 0, 16);
}

function RefreshBans() {
global $bd_names;

	/* Default ban until time */
	BD("DELETE FROM {$bd_names['ip_banning']} WHERE (ban_until='0000-00-00 00:00:00') AND (time_start<NOW()-INTERVAL ".((int) sqlConfigGet('next-reg-time'))." HOUR)");
	
	BD("DELETE FROM {$bd_names['ip_banning']} WHERE (ban_until<>'0000-00-00 00:00:00') AND (ban_until<NOW())");					
}

function vtxtlog($string) {
global $config;

if (!$config['log']) return;

$log_file = MCR_ROOT.'log.txt';

	if (file_exists($log_file) and round(filesize ($log_file) / 1048576) >= 50) unlink($log_file);

	if ( !$fp = fopen($log_file,'a') ) exit('[vtxtlog]  --> '.$log_file.' <-- '.lng('WRITE_FAIL'));
	
	fwrite($fp, date("H:i:s d-m-Y").' < '.$string.PHP_EOL); 
	fclose($fp);	
}

function ActionLog($last_info = 'default_action') {
global $config, $bd_names;

	$ip = GetRealIp();
	BD("DELETE FROM `{$bd_names['action_log']}` WHERE `first_time` < NOW() - INTERVAL {$config['action_time']} SECOND");	

	$sql  = "INSERT INTO `{$bd_names['action_log']}` (IP, first_time, last_time, query_count, info) ";
	$sql .= "VALUES ('".TextBase::SQLSafe($ip)."', NOW(), NOW(), 1, '".TextBase::SQLSafe($last_info)."') ";
	$sql .= "ON DUPLICATE KEY UPDATE `last_time` = NOW(), `query_count` = `query_count` + 1, `info` = '".TextBase::SQLSafe($last_info)."' ";
	
	BD($sql);	
	
	$result = BD("SELECT `query_count` FROM `{$bd_names['action_log']}` WHERE `IP`='".TextBase::SQLSafe($ip)."'"); 
	$line = mysql_fetch_array($result, MYSQL_NUM);
	
	$query_count = (int) $line[0];
	if ($query_count > $config['action_max']) {
	
	BD("DELETE FROM `{$bd_names['action_log']}` WHERE `IP` = '".TextBase::SQLSafe($ip)."'");
	
	RefreshBans();
	
	$sql  = "INSERT INTO {$bd_names['ip_banning']} (IP, time_start, ban_until, ban_type, reason) ";
	$sql .= "VALUES ('".TextBase::SQLSafe($ip)."', NOW(), NOW()+INTERVAL ".TextBase::SQLSafe($config['action_ban'])." SECOND, '2', 'Many BD connections (".$query_count.") per time') ";
	$sql .= "ON DUPLICATE KEY UPDATE `ban_type` = '2', `reason` = 'Many BD connections (".$query_count.") per time' ";
	
	BD($sql);	
	}
	
	return $query_count;
}

function CanAccess($ban_type = 1) {
global $link, $bd_names;

	$ip = GetRealIp(); 
	$ban_type = (int) $ban_type;
	
	$result = BD("SELECT COUNT(*) FROM `{$bd_names['ip_banning']}` WHERE `IP`='".TextBase::SQLSafe($ip)."' AND `ban_type`='".$ban_type."' AND `ban_until` <> '0000-00-00 00:00:00' AND `ban_until` > NOW()"); 
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$num = (int)$line[0];

	if ($num) {
	
		mysql_close( $link );
		
		if ( $ban_type == 2 ) exit('(-_-)zzZ <br>'.lng('IP_BANNED'));
		return false;
	}
	
	return true;					
}
?>