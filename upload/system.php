<?php

error_reporting(E_ALL);

$user = false;
$link = false;

$mcr_tools = array();

define('MCR_ROOT', dirname(__FILE__) . '/');
define('MCR_LANG', 'ru_RU');

loadTool('base.class.php');

if (!file_exists(MCR_ROOT . 'config.php')) {
    header("Location: install/install.php");
    exit;
}

require(MCR_ROOT . 'instruments/locale/' . MCR_LANG . '.php');
require(MCR_ROOT . 'config.php');

if (!isset($config['db_driver'])) {
    $config['db_driver'] = 'mysql';
}

require(MCR_ROOT . 'instruments/auth/' . $config['p_logic'] . '.php');

define('MCRAFT', MCR_ROOT . $site_ways['mcraft']);
define('MCR_STYLE', MCR_ROOT . $site_ways['style']);

define('STYLE_URL', $site_ways['style']); // deprecated
define('DEF_STYLE_URL', STYLE_URL . View::def_theme . '/');

define('BASE_URL', $config['s_root']);

date_default_timezone_set($config['timezone']);

/**
 * @deprecated since v2.35
 */
function BD($query)
{
    $resultStatement = getDB()->ask($query);
    return $resultStatement->getResult();
}

/**
 * @deprecated since v2.35
 */
function BDConnect($log_script = 'default')
{
    global $link;

    if (!$link)
        DBinit($log_script);
}

function DBinit($log_script = 'default')
{
    global $link, $config;

    if ($link)
        return;
    
    loadTool('databaseInterface.class.php', 'database/');
    loadTool('statementInterface.class.php', 'database/');
    
    if ( $config['db_driver'] != 'pdo') {
        loadTool('mysqlDriverBase.class.php', 'database/' );
        loadTool('mysqlDriverStm.class.php', 'database/' ); 
    }
    
    loadTool('module.class.php', 'database/' . $config['db_driver'] . '/');
    loadTool('statement.class.php', 'database/' . $config['db_driver'] . '/' );
    
    $class = $config['db_driver'] . 'Driver';
    $link = new $class();

    try {    
        if (!empty($config['db_file'])) {
        
            $link->connect(array('file' => $config['db_file']));
            
        } else {
        
            $link->connect(array(
                'host' => $config['db_host'], 
                'port' => $config['db_port'], 
                'login' => $config['db_login'], 
                'password' => $config['db_passw'], 
                'db' => $config['db_name']
            ));        
        }        
    } catch (Exception $e) {
        exit($e->getMessage());
    }

    if ($log_script and $config['action_log'])
        ActionLog($log_script);
        
    CanAccess(2);
}

/**
 * 
 * @global DataBaseInterface $link
 * @return DataBaseInterface
 */
function getDB()
{
    global $link;

    if ($link === false) {
        DBinit();
    }

    return $link;
}

/* Системные функции */

function loadTool($name, $sub_dir = '')
{
    global $mcr_tools;

    if (in_array($name, $mcr_tools))
        return;

    $mcr_tools[] = $name;

    require( MCR_ROOT . 'instruments/' . $sub_dir . $name);
}

function lng($key, $lang = false)
{
    global $MCR_LANG;

    return isset($MCR_LANG[$key]) ? $MCR_LANG[$key] : $key;
}

function tmp_name($folder, $pre = '', $ext = 'tmp')
{
    $name = $pre . time() . '_';

    for ($i = 0; $i < 8; $i++)
        $name .= chr(rand(97, 121));

    $name .= '.' . $ext;

    return (file_exists($folder . $name)) ? tmp_name($folder, $pre, $ext) : $name;
}

function POSTGood($post_name, $format = array('png'))
{
    if (empty($_FILES[$post_name]['tmp_name']) or
            $_FILES[$post_name]['error'] != UPLOAD_ERR_OK or
            !is_uploaded_file($_FILES[$post_name]['tmp_name']))
        return false;

    $extension = strtolower(substr($_FILES[$post_name]['name'], 1 + strrpos($_FILES[$post_name]['name'], ".")));

    if (is_array($format) and !in_array($extension, $format))
        return false;

    return true;
}

function POSTSafeMove($post_name, $tmp_dir = false)
{
    if (!POSTGood($post_name, false))
        return false;

    if (!$tmp_dir)
        $tmp_dir = MCRAFT . 'tmp/';

    if (!is_dir($tmp_dir)) {
        $back = umask(0);
        mkdir($tmp_dir, 0775, true);
        umask($back);
    }

    $tmp_file = tmp_name($tmp_dir);
    if (!move_uploaded_file($_FILES[$post_name]['tmp_name'], $tmp_dir . $tmp_file)) {
        vtxtlog('[POSTSafeMove] --> "' . $tmp_dir . '" <-- ' . lng('WRITE_FAIL'));
        return false;
    }

    return array(
        'tmp_name' => $tmp_file, 
        'name' => $_FILES[$post_name]['name'], 
        'size_mb' => round($_FILES[$post_name]['size'] / 1024 / 1024, 2)
    );
}

function randString($pass_len = 50)
{
    $allchars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $string = "";

    mt_srand((double) microtime() * 1000000);

    for ($i = 0; $i < $pass_len; $i++)
        $string .= $allchars{ mt_rand(0, strlen($allchars) - 1) };

    return $string;
}

function sqlConfigGet($type)
{
    global $bd_names;

    if (!in_array($type, ItemType::$SQLConfigVar))
        return false;

    $line = getDB()->fetchRow("SELECT `value` FROM `{$bd_names['data']}` "
            . "WHERE `property`=:type", array('type' => $type), 'num');

    return ($line) ? $line[0] : false;
}

function sqlConfigSet($type, $value)
{
    global $bd_names;

    if (!in_array($type, ItemType::$SQLConfigVar))
        return false;

    $result = getDB()->ask("INSERT INTO `{$bd_names['data']}` (value,property) "
            . "VALUES (:value, :type) "
            . "ON DUPLICATE KEY UPDATE `value`=:value2", array(
        'value' => $value,
        'type' => $type,
        'value2' => $value
    ));

    return true;
}

function InputGet($key, $method = 'post', $type = 'string') {
    return Filter::input($key, $method, $type);
}

function GetRealIp()
{

    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ip = $_SERVER['HTTP_CLIENT_IP'];

    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else
        $ip = $_SERVER['REMOTE_ADDR'];

    return substr($ip, 0, 16);
}

function RefreshBans()
{
    global $bd_names;

    /* Default ban until time */
    getDB()->ask("DELETE FROM {$bd_names['ip_banning']} "
            . "WHERE (ban_until='0000-00-00 00:00:00') "
            . "AND (time_start<NOW()-INTERVAL " . ((int) sqlConfigGet('next-reg-time')) . " HOUR)");

    getDB()->ask("DELETE FROM {$bd_names['ip_banning']} "
            . "WHERE (ban_until<>'0000-00-00 00:00:00') "
            . "AND (ban_until<NOW())");
}

function vtxtlog($string)
{
    global $config;

    if (!$config['log'])
        return;

    $log_file = MCR_ROOT . 'log.txt';

    if (file_exists($log_file) and round(filesize($log_file) / 1048576) >= 50)
        unlink($log_file);

    if (!$fp = fopen($log_file, 'a'))
        exit('[vtxtlog]  --> ' . $log_file . ' <-- ' . lng('WRITE_FAIL'));

    fwrite($fp, date("H:i:s d-m-Y") . ' < ' . $string . PHP_EOL);
    fclose($fp);
}

function tokenTool($mode = 'set')
{
    global $content_js;

    if (!isset($_SESSION)) {
        session_start();
    }

    if ($mode == 'check') {

        if (empty($_SESSION['token_data']) or
            $_SESSION['token_data'] !== Filter::input('token_data')) {

            if (isset($_SESSION['token_data']))
                unset($_SESSION['token_data']);
            exit(lng('TOKEN_FAIL'));

            return false;
        }

        unset($_SESSION['token_data']);
        return true;
    } elseif ($mode == 'set') {

        $_SESSION['token_data'] = randString(32);
        $content_js .= '<script type="text/javascript">var token_data = "' . $_SESSION['token_data'] . '";</script>';
        return true;
    } elseif ($mode == 'setinput') {

        $_SESSION['token_data'] = randString(32);
        return '<input type="hidden" name="token_data" id="token_data" value="' . $_SESSION['token_data'] . '" />';
    } else { 
        $_SESSION['token_data'] = randString(32);
        return $_SESSION['token_data'];
    }
}

function ActionLog($last_info = 'default_action')
{
    global $config, $bd_names;

    $ip = GetRealIp();
    getDB()->ask("DELETE FROM `{$bd_names['action_log']}` "
            . "WHERE `first_time` < NOW() - INTERVAL {$config['action_time']} SECOND");

    $sql = "INSERT INTO `{$bd_names['action_log']}` (IP, first_time, last_time, query_count, info) "
            . "VALUES (:ip, NOW(), NOW(), 1, :info) "
            . "ON DUPLICATE KEY UPDATE "
            . "`last_time` = NOW(), "
            . "`query_count` = `query_count` + 1, "
            . "`info` = :info2";

    getDB()->ask($sql, array('info' => $last_info, 'ip' => $ip, 'info2' => $last_info));

    $line = getDB()->fetchRow("SELECT `query_count` FROM `{$bd_names['action_log']}` "
            . "WHERE `IP`=:ip", array('ip' => $ip), 'num');

    $query_count = (int) $line[0];
    if ($query_count > $config['action_max']) {

        getDB()->ask("DELETE FROM `{$bd_names['action_log']}` WHERE `IP`=:ip", array('ip' => $ip));

        RefreshBans();

        $sql = "INSERT INTO {$bd_names['ip_banning']} (IP, time_start, ban_until, ban_type, reason) "
                . "VALUES (:ip, NOW(), NOW()+INTERVAL {$config['action_ban']} SECOND, '2', 'Many BD connections (" . $query_count . ") per time') "
                . "ON DUPLICATE KEY UPDATE `ban_type` = '2', `reason` = 'Many BD connections (" . $query_count . ") per time' ";

        getDB()->ask($sql, array('ip' => $ip));
    }

    return $query_count;
}

function CanAccess($ban_type = 1)
{
    global $bd_names;

    $ip = GetRealIp();
    $ban_type = (int) $ban_type;

    $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$bd_names['ip_banning']}` "
            . "WHERE `IP`=:ip AND `ban_type`='" . $ban_type . "' "
            . "AND `ban_until` <> '0000-00-00 00:00:00' AND `ban_until` > NOW()", array('ip' => $ip), 'num');

    $num = (int) $line[0];

    if ($num) {

        getDB()->close();

        if ($ban_type == 2)
            exit('(-_-)zzZ <br>' . lng('IP_BANNED'));
        return false;
    }

    return true;
}
