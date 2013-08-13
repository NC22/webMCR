<?php
if (!defined('MCR')) exit;

ob_start();

if (!empty($user)) {
  
   if ($mode == 'control') 
   include View::Get('side.html', 'admin/');  
   include View::Get('mini_profile.html');    
	
} else {
	
	if ($mode == 'register') $addition_events .= "BlockVisible('reg-box',true); BlockVisible('login-box',false);";

	include View::Get('login.html');		    
}

$content_side .= ob_get_clean();

loadTool('monitoring.class.php');

$servManager = new ServerManager();
$content_servers = $servManager->Show('side');

unset($servManager);
?>