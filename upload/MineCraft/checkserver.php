<?php

require('../system.php');

$user = Filter::input('user', 'get');
$serverid = Filter::input('serverId', 'get');

if (empty($user) or empty($serverid)) {
    vtxtlog("[checkserver.php] checkserver process [GET parameter empty] [ " . ((empty($user)) ? 'LOGIN ' : '') . ((empty($serverid)) ? 'SERVERID ' : '') . "]");
    exit('NO');
}
loadTool('user.class.php');
DBinit('checkserver');

if (!preg_match("/^[a-zA-Z0-9_-]+$/", $user) or
        !preg_match("/^[a-z0-9_-]+$/", $serverid)) {

    vtxtlog("[checkserver.php] error checkserver process [info login " . $user . " serverid " . $serverid . "]");
    exit('NO');
}

$sql = "SELECT  COUNT(*) FROM {$bd_names['users']} "
        . "WHERE `{$bd_users['login']}`=:user AND `{$bd_users['server']}`=:serverid";

$result = getDB()->fetchRow($sql, array('user' => $user, 'serverid' => $serverid), 'num');

if ((int) $result[0]) {

    $user_login = new User($user, $bd_users['login']);
    $user_login->gameLoginConfirm();
    vtxtlog("[checkserver.php] Server Test [Success]");
    exit('YES');
}

vtxtlog("[checkserver.php] [User not found] User [$user] Server ID [$serverid]");
exit('NO');
