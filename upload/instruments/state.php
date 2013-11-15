<?php
require('../system.php');

loadTool('ajax.php');
loadTool('monitoring.class.php');

if (empty($_POST['id'])) exit;
$id = (int)$_POST['id'];

$now = false;

if (isset($_POST['now']) and !empty($user) and $user->lvl() >= 15) 

$now = true;

BDConnect('monitoring');

$server = new Server($id, 'serverstate/');
$server->UpdateState($now);
$server->ShowInfo();
?>