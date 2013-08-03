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

function generateSessionId(){
    srand(time());
    $randNum = rand(1000000000, 2147483647).rand(1000000000, 2147483647).rand(0,9);
    return $randNum;
}

function logExit($text, $output = "Bad login") {
  vtxtlog($text); exit($output);
}


if (($_SERVER['REQUEST_METHOD'] == 'POST' ) && (stripos($_SERVER["CONTENT_TYPE"], "application/json") === 0)) {
    $json = json_decode($HTTP_RAW_POST_DATA);
    
} else {
    logExit("Bad request method. POST/json required", "Bad request method. POST/json required");
}

if ( empty($json->username) or empty($json->password) or empty($json->clientToken)) 

	logExit("[auth16x.php] login process [Empty input] [ ".((empty($json->username))? 'LOGIN ':'').((empty($json->password))? 'PASSWORD ':'').((empty($json->clientToken))? 'clientToken ':'')."]");

	loadTool('user.class.php'); 
	BDConnect('auth');

$login = $json->username; $password = $json->password; $clientToken = $json->clientToken;

if (!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,4}$/", $login)    or
	!preg_match("/^[a-zA-Z0-9_-]+$/", $password)  or
	!preg_match("/^[a-f0-9-]+$/", $clientToken)) 
		
	logExit("[auth16x.php] login process [Bad symbols] User [$login] Password [$password] clientToken [$clientToken]");		

	$auth_user = new User($login, $bd_users['email']);
	
	if ( !$auth_user->id() ) logExit("[auth16.php] login process [Unknown user] User [$login] Password [$password]");
	if ( $auth_user->lvl() <= 1 ) exit("Bad login");
	if ( !$auth_user->authenticate($password) ) logExit("[auth16.php] login process [Wrong password] User [$login] Password [$password]");

    $sessid = generateSessionId();
    BD("UPDATE `{$bd_names['users']}` SET `{$bd_users['session']}`='".TextBase::SQLSafe($sessid)."' WHERE `{$bd_users['email']}`='".TextBase::SQLSafe($login)."'");
    BD("UPDATE `{$bd_names['users']}` SET `{$bd_users['clientToken']}`='".TextBase::SQLSafe($clientToken)."' WHERE `{$bd_users['email']}`='".TextBase::SQLSafe($login)."'");

	vtxtlog("[auth16.php] login process [Success] User [$login] Session [$sessid] clientToken[$clientToken]");			
	
        $profile = array ( 'id' => $auth_user->id(), 'name' => $auth_user->name() ) ;
        
        $responce = array(
            'clientToken' => $clientToken, 
            'accessToken' => $sessid, 
            'availableProfiles' => array ( 0 => $profile), 
            'selectedProfile' => $profile);
        
        exit(json_encode($responce));
?>