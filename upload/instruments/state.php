<?php
require('../system.php');

loadTool('ajax.php');
loadTool('monitoring.class.php');

if (empty($_POST['id'])) exit;
$id = (int)$_POST['id'];

DBinit('monitoring');

$server = new Server($id, 'serverstate/');
$server->UpdateState();
$server->ShowInfo();
