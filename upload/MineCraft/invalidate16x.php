<?php

require('../system.php');

function generateSessionId() {
    srand(time());
    $randNum = rand(1000000000, 2147483647) . rand(1000000000, 2147483647) . rand(0, 9);
    return $randNum;
}

function logExit($text, $output = "Bad login") {
    vtxtlog($text);
    exit($output);
}

if (($_SERVER['REQUEST_METHOD'] == 'POST' ) && (stripos($_SERVER["CONTENT_TYPE"], "application/json") === 0)) {
    $json = json_decode($HTTP_RAW_POST_DATA);
} else {
    logExit("Bad request method. POST/json required", "Bad request method. POST/json required");
}

if (empty($json->accessToken) or empty($json->clientToken))
    logExit("[invalidate16x.php] invalidate process [Empty input] [ " . ((empty($json->accessToken)) ? 'Session ' : '') . ((empty($json->clientToken)) ? 'clientToken ' : '') . "]");

require(MCR_ROOT . 'instruments/user.class.php');
BDConnect('auth');

$sessionid = $json->accessToken;
$clientToken = $json->clientToken;

if (!preg_match("/^[a-f0-9-]+$/", $sessionid) or
        !preg_match("/^[a-f0-9-]+$/", $clientToken))
    logExit("[invalidate16x.php] login process [Bad symbols] Session [$sessionid] clientToken [$clientToken]");
vtxtlog("SELECT `{$bd_users['email']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['session']}`='" . TextBase::SQLSafe($sessionid) . "' AND `{$bd_users['clientToken']}`='" . TextBase::SQLSafe($clientToken) . "'");
$result = BD("SELECT `{$bd_users['email']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['session']}`='" . TextBase::SQLSafe($sessionid) . "' AND `{$bd_users['clientToken']}`='" . TextBase::SQLSafe($clientToken) . "'");

if (mysql_num_rows($result) != 1)
    logExit("[invalidate16x.php] invalidate process, wrong accessToken/clientToken pair");

$line = mysql_fetch_array($result, MYSQL_NUM);
$login = $line[0];

$auth_user = new User($login, $bd_users['email']);

BD("UPDATE `{$bd_names['users']}` SET `{$bd_users['session']}`='' WHERE `{$bd_users['email']}`='" . TextBase::SQLSafe($login) . "'");


vtxtlog("[invalidate16x.php] refresh process [Success] User [$login] Invalidate Session [$sessionid] clientToken[$clientToken]");

exit();
?>