<?php
header ("Content-type: image/png");
require('./system.php');

$use_default_skin = true;

if ( (isset($_GET["mini"]) and (int)$_GET['mini'] != -1 ) or isset($_GET["user_id"]) or isset($_GET["user_name"]) and !isset($_GET['female'])) { 

	BDConnect();
	require(MCR_ROOT.'instruments/user.class.php');
	require(MCR_ROOT.'instruments/skin.class.php');	
	$use_default_skin = false; 
}

$mode	= 1;
$male	= (!empty($_GET['female']))? false : true;

$tmp_id = false; $tmp_name  = false; $tmp_user  = false;

$user_name = false;

$way_skin   = ''; $way_cloak  = '';
	
    if ( isset($_GET["mini"])) { 
	
	$tmp_id = (int)$_GET['mini']; 
	$mode = 2; 	
	}
elseif ( isset($_GET["user_id"])	)   $tmp_id = (int)$_GET['user_id']; 
elseif ( isset($_GET["user_name"])	)   $tmp_name = $_GET['user_name']; 

if ($tmp_id <= 0) { $tmp_id = false; $use_default_skin = true; }

if ( $tmp_id or $tmp_name ) {

	if ( $tmp_id )  $tmp_user = new User($tmp_id, $bd_users['id']);
	else            $tmp_user = new User($tmp_name, $bd_users['login']);
  
	if ( !$tmp_user->id() ) exit;	
  
	$use_default_skin = $tmp_user->defaultSkinTrigger();	
	$user_name = $tmp_user->name();	
	
    if ($tmp_user->isFemale()) $male = false;	
}

if ( $user_name ) {

	$way_skin        = MCRAFT.$site_ways['skins'].$user_name.'.png';
	$way_cloak       = MCRAFT.$site_ways['cloaks'].$user_name.'.png';
	$way_buffer      = MCRAFT.'tmp/skin_buffer/'.$user_name.'.png';
	$way_buffer_mini = MCRAFT.'tmp/skin_buffer/'.$user_name.'_Mini.png';    
}
	
if (!$way_cloak or !file_exists($way_cloak)) $way_cloak = false;

if ($use_default_skin or !$way_skin or !file_exists($way_skin)) {

	$way_skin        = MCRAFT.'tmp/default_skins/Char'.((!$male)? '_female':'').'.png';
    $way_buffer_mini = MCRAFT.'tmp/skin_buffer/default/Char_Mini'.((!$male)? '_female':'').'.png';
	
	if ( !$way_cloak )	
	$way_buffer = MCRAFT.'tmp/skin_buffer/default/Char'.((!$male)? '_female':'').'.png';
}

if (!$config['sbuffer']) {
	
	$image = ($mode == 1)? skinGenerator2D::createPreview($way_skin, $way_cloak) :  skinGenerator2D::createHead($way_skin);
	imagepng($image);
	imagedestroy($image);	
	
} else {
	
	if (($mode == 1 and !file_exists($way_buffer)) or ($mode == 2 and !file_exists($way_buffer_mini))) 	
	
		($mode == 1)? skinGenerator2D::savePreview($way_buffer, $way_skin, $way_cloak) : skinGenerator2D::saveHead($way_buffer_mini, $way_skin);		
	
	readfile(($mode == 1) ? $way_buffer : $way_buffer_mini);	
}
?>