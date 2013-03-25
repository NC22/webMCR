<?php
require('../system.php');
require(MCR_ROOT.'instruments/ajax.php');
require(MCR_ROOT.'instruments/monitoring.class.php');

if (empty($_POST['id'])) exit;
$id = (int)$_POST['id'];

$now = false;

if (isset($_POST['now']) and !empty($user) and $user->lvl() >= 15) 

$now = true;

BDConnect('monitoring');

$server = new Server($id);
$server->UpdateState($now);
$server->ShowInfo();
?>