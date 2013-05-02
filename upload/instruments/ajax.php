<?php
header("HTTP/1.0 200 OK"); 
header("HTTP/1.1 200 OK"); 
header("Cache-Control: no-cache, must-revalidate, max-age=0"); 
header("Expires: 0"); 
header("Pragma: no-cache");
header("Content-type: text/html; charset=UTF-8");

$ajax_message = array('code' => 0, 'message' => '');

function CaptchaCheck($exit_mess = 2) { 

	if ( empty($_SESSION['code']) or 
         empty($_POST['antibot']) or 
         $_SESSION['code'] != (int)$_POST['antibot'] ) {
       
            if (isset($_SESSION['code'])) unset($_SESSION['code']);
            aExit($exit_mess, 'Защитный код введен не верно.');

    }
	unset($_SESSION['code']);
}

function aExit($code, $mess = 'error') {
global $ajax_message;

  $ajax_message['code']    = $code;
  $ajax_message['message'] = ($mess == 'error')? $mess.' code: '.$code : $mess;
    
  // exit(str_replace('\/','/',json_encode($ajax_message, JSON_HEX_QUOT | JSON_HEX_APOS))); JSON_HEX_QUOT | JSON_HEX_TAG
  if (defined('JSON_HEX_QUOT')) $result = json_encode($ajax_message, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG);
  else $result = json_encode($ajax_message);
  
exit($result);
}