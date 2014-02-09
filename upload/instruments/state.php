<?php
require('../system.php');

loadTool('ajax.php');
loadTool('monitoring.class.php');

$id = Filter::input('id', 'post', 'int', true) or exit;

DBinit('monitoring');

$server = new Server($id, 'serverstate/');
$server->UpdateState();
$server->ShowInfo();
