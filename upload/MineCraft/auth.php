<?php

require('../system.php');

function generateSessionId()
{
    srand(time());
    $randNum = rand(1000000000, 2147483647) . rand(1000000000, 2147483647) . rand(0, 9);
    return $randNum;
}

function logExit($text, $output = "Bad login")
{
    vtxtlog($text);
    exit($output);
}

$login = Filter::input('user');
$password = Filter::input('password');
$ver = Filter::input('version');

if (empty($password) or empty($ver) or empty($login))
    logExit("[auth.php] login process [Empty input] [ " . ((empty($login)) ? 'LOGIN ' : '') . ((empty($password)) ? 'PASSWORD ' : '') . ((empty($ver)) ? 'VER ' : '') . "]");

loadTool('user.class.php');
DBinit('auth');

if (!preg_match("/^[a-zA-Z0-9_-]+$/", $login) or
    !preg_match("/^[a-zA-Z0-9_-]+$/", $password) or
    !preg_match("/^[0-9]+$/", $ver))
    logExit("[auth.php] login process [Bad symbols] User [$login] Password [$password] Ver [$ver]");

if ((int) sqlConfigGet('launcher-version') != (int) $ver)
    logExit("[auth.php] login process [Old version] ver " . $ver, "Old version");

$auth_user = new User($login, $bd_users['login']);

if (!$auth_user->id())
    logExit("[auth.php] login process [Unknown user] User [$login] Password [$password]");
if ($auth_user->lvl() <= 1)
    exit("Bad login");
if (!$auth_user->authenticate($password))
    logExit("[auth.php] login process [Wrong password] User [$login] Password [$password]");

$sessid = generateSessionId();
getDB()->ask("UPDATE `{$bd_names['users']}` SET "
        . "`{$bd_users['session']}`=:session "
        . "WHERE `{$bd_users['login']}`=:login", array('session' => $sessid, 'login' => $login));

vtxtlog("[auth.php] login process [Success] User [$login] Session [$sessid]");

exit(sqlConfigGet('latest-game-build') . ':' . md5($auth_user->name()) . ':' . $auth_user->name() . ':' . $sessid . ':');
