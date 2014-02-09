<?php

require('../system.php');

$login = Filter::input('user', 'get');
$serverid = Filter::input('serverId', 'get');
$sessionid = Filter::input('sessionId', 'get');

if (empty($sessionid) or empty($serverid) or empty($login)) {

    vtxtlog("[joinserver.php] join process [GET parameter empty] [ " . (empty($sessionid) ? 'SESSIONID ' : '') . ((empty($login)) ? 'USER ' : '') . ((empty($serverid)) ? 'SERVERID ' : '') . "]");
    exit('Bad login');
}

loadTool('user.class.php');
DBinit('joinserver');

$sessionidv16 = explode(":", $sessionid);

if (($sessionidv16[0] == "token") && ($sessionidv16[2] == "2")) {
    $sessionid = $sessionidv16[1];
}

if (!preg_match("/^[a-zA-Z0-9_-]+$/", $login) or
        !preg_match("/^[0-9]+$/", $sessionid) or
        !preg_match("/^[a-z0-9_-]+$/", $serverid)) {

    vtxtlog("[joinserver.php] error while login process [input login " . $login . " sessionid " . $sessionid . " serverid " . $serverid . "]");
    exit('Bad login');
}

$tmp_user = new User($login, $bd_users['login']);
if ($tmp_user->id() === false or $tmp_user->name() !== $login) {

    vtxtlog("[joinserver.php] Bad login register");
    exit('Bad login');
}

$sql = "SELECT COUNT(*) FROM `{$bd_names['users']}` "
        . "WHERE `{$bd_users['session']}`=:session "
        . "AND `{$bd_users['login']}`=:login "
        . "AND `{$bd_users['server']}`=:server";

$result = getDB()->fetchRow($sql, array(
    'session' => $sessionid,
    'login' => $tmp_user->name(),
    'server' => $serverid
        ), 'num');

if ((int) $result[0] == 1) {
    vtxtlog('[joinserver.php] join Server [Result] Relogin OK');
    exit('OK');
}
$sql = "UPDATE `{$bd_names['users']}` SET `{$bd_users['server']}`=:server "
        . "WHERE `{$bd_users['session']}`=:session "
        . "AND `{$bd_users['login']}`=:login ";

$result = getDB()->ask($sql, array(
    'session' => $sessionid,
    'login' => $tmp_user->name(),
    'server' => $serverid
        ));

if ($result->rowCount() == 1) {
    vtxtlog('[joinserver.php] join Server [Result] login OK');
    exit('OK');
}

vtxtlog("[joinserver.php] join Server [Result] Bad Login - input Session [$sessionid] User [$login] Server [$serverid]");
exit('Bad login');
