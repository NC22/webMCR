<?php
if (!defined('MCR')) exit;

require_once(MCR_ROOT.'instruments/monitoring.class.php');

ob_start();

if (!empty($user)) {
  
   if ($mode == 'control') 
   include MCR_STYLE.'admin/side.html';  
   include MCR_STYLE.'mineprofil.html';    
	
} else {
	
	if ($mode == 'register') $addition_events .= "BlockVisible('reg-box',true); BlockVisible('login-box',false);";

	include MCR_STYLE.'login.html';		    
}

$content_side .= ob_get_clean();
?>