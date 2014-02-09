<?php
header("HTTP/1.0 200 OK"); 
header("HTTP/1.1 200 OK"); 
header("Cache-Control: no-cache, must-revalidate, max-age=0"); 
header("Expires: 0"); 
header("Pragma: no-cache");
header("Content-type: text/html; charset=UTF-8");

$ajax_message = array('code' => 0, 'message' => '');

function escapeJsonString($value) { // экранирование строки JSON

    // list from json.org: (\b backspace, \f formfeed)    
    $escapers =     array("\\",     "/",   "\"",  "\n",  "\r",  "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t",  "\\f",  "\\b");
	
    $result = str_replace($escapers, $replacements, $value);
    return $result;
  }
  
function CaptchaCheck($exit_mess = 2, $ajaxExit = true, $post_name = 'antibot')
{
    if (!isset($_SESSION))
        session_start();
    
    $input = Filter::input($post_name, 'post', 'int', true);
    
    if (empty($_SESSION['code']) or
        $input === false or
        $_SESSION['code'] != $input) {

        if (isset($_SESSION['code']))
            unset($_SESSION['code']);
        if ($ajaxExit)
            aExit($exit_mess, lng('CAPTCHA_FAIL'));


        return false;
    }
    unset($_SESSION['code']);
    return true;
}

function aExit($code, $mess = 'error')
{
    global $ajax_message;

    $iframe = Filter::input('json_iframe', 'post', 'bool');

    $ajax_message['code'] = $code;
    $ajax_message['message'] = ($mess == 'error') ? $mess . ' code: ' . $code : $mess;

    if (defined('JSON_HEX_QUOT'))
        $result = json_encode($ajax_message, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG);
    else
        $result = json_encode($ajax_message);

    if ($iframe) {

        $result = escapeJsonString($result);
        $result = '<html><head><title>jnone</title><script type="text/javascript"> var json_response = "' . $result . '"</script></head><body></body></html>';
    }

    exit($result);
}
