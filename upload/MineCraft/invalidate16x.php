<?php
/*
    This file is part of webMCR.
 
    webMCR is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webMCR is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with webMCR.  If not, see <http://www.gnu.org/licenses/>.

 */

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

loadTool('user.class.php');
DBinit('auth');

$sessionid = $json->accessToken;
$clientToken = $json->clientToken;

if (!preg_match("/^[a-f0-9-]+$/", $sessionid) or
        !preg_match("/^[a-f0-9-]+$/", $clientToken))
    logExit("[invalidate16x.php] login process [Bad symbols] Session [$sessionid] clientToken [$clientToken]");

$sql    = "SELECT `{$bd_names['email']}` FROM `{$bd_names['users']}` "
        . "WHERE `{$bd_users['session']}`=:sessionid AND `{$bd_users['clientToken']}`=:token";

$result = getDB()->fetchRow($sql, array('sessionid' => $sessionid, 'token' => $clientToken), 'num');

if (!$result)
    logExit("[invalidate16x.php] invalidate process, wrong accessToken/clientToken pair");

$login = $result[0];

$auth_user = new User($login, $bd_users['email']);

$sql = "UPDATE `{$bd_names['users']}` SET `{$bd_users['session']}`='' "
     . "WHERE `{$bd_users['email']}`=:email";

getDB()->ask($sql, array('email' => $login));

vtxtlog("[invalidate16x.php] refresh process [Success] User [$login] Invalidate Session [$sessionid] clientToken[$clientToken]");

exit();
