<?php
if (!defined('MCR')) exit;

require_once(MCR_ROOT.'instruments/monitoring.class.php');

ob_start();

if (!empty($user)) {
  
   if ($mode == 'control') 
   include View::Get('side.html', 'admin/');  
   include View::Get('mineprofil.html');    
	
} else {
	
	if ($mode == 'register') $addition_events .= "BlockVisible('reg-box',true); BlockVisible('login-box',false);";

	include View::Get('login.html');		    
}

$content_side .= ob_get_clean();
?>