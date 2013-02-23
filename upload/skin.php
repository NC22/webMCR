<?php
header ("Content-type: image/png");
require('./system.php');

if ( (isset($_GET["mini"]) and (int)$_GET['mini'] != -1 ) or isset($_GET["user_id"]) or isset($_GET["user_name"])) { 

	BDConnect();
	require(MCR_ROOT.'instruments/user.class.php');	
}

function imageflip(&$result, &$img, $rx = 0, $ry = 0, $x = 0, $y = 0, $size_x = null, $size_y = null) {
 if ($size_x  < 1) $size_x = imagesx($img);
 if ($size_y  < 1) $size_y = imagesy($img);
 
 imagecopyresampled($result, $img, $rx, $ry, ($x + $size_x-1), $y, $size_x, $size_y, 0-$size_x, $size_y);
}

function mini($way_skin, $mp = 1, $size = 151) {

if (!file_exists($way_skin)  or !$mp) return false;

     $im = @imagecreatefrompng($way_skin);
if (!$im)  return false; 

$av = imagecreatetruecolor($size,$size);

imagecopyresized($av,$im,0,0,8 *$mp,8 *$mp,$size,$size,8 *$mp,8 *$mp); 
imagecopyresized($av,$im,0,0,40*$mp,8 *$mp,$size,$size,8 *$mp,8 *$mp);  

imagedestroy($im);

return $av;
}

function create_skin($way_skin,$way_cloak = false,$mp = 1, $mp_cloak = 1) {

if (!file_exists($way_skin)  or !$mp)       return false;
if (!$way_cloak or !file_exists($way_cloak) or !$mp_cloak) $way_cloak = false;
else {
          $cloak = @imagecreatefrompng($way_cloak);
     if (!$cloak) $way_cloak = false;
}

     $skin = @imagecreatefrompng($way_skin);
if (!$skin)  return false; 

$mp_x = 32 *$mp; $mp_y = 32 *$mp; $mp_x_h = $mp_x / 2;

$preview = imagecreatetruecolor($mp_x, $mp_y);

$transparent = imagecolorallocatealpha($preview, 255, 255, 255, 127);
imagefill($preview, 0, 0, $transparent);

if ($way_cloak)
imagecopyresized($preview, $cloak, 3 *$mp, 8 *$mp, 12 *$mp_cloak, 1 *$mp_cloak, 10 *$mp, 16 *$mp, 10 *$mp_cloak, 16 *$mp_cloak);

imagecopy($preview, $skin, 4 *$mp, 0  *$mp, 8  *$mp, 8  *$mp, 8 *$mp, 8  *$mp);
imagecopy($preview, $skin, 0 *$mp, 8  *$mp, 44 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
imageflip($preview, $skin, 12*$mp, 8  *$mp, 44 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
imagecopy($preview, $skin, 4 *$mp, 8  *$mp, 20 *$mp, 20 *$mp, 8 *$mp, 12 *$mp);
imagecopy($preview, $skin, 4 *$mp, 20 *$mp, 4  *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
imageflip($preview, $skin, 8 *$mp, 20 *$mp, 4  *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
imagecopy($preview, $skin, 4 *$mp, 0  *$mp, 40 *$mp, 8  *$mp, 8 *$mp, 8  *$mp);

imagecopy($preview, $skin, $mp_x_h + 4  *$mp, 8  *$mp, 32 *$mp, 20 *$mp, 8 *$mp, 12 *$mp);
imagecopy($preview, $skin, $mp_x_h + 4  *$mp, 0  *$mp, 24 *$mp, 8  *$mp, 8 *$mp, 8  *$mp);
imageflip($preview, $skin, $mp_x_h + 0  *$mp, 8  *$mp, 52 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
imagecopy($preview, $skin, $mp_x_h + 12 *$mp, 8  *$mp, 52 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
imageflip($preview, $skin, $mp_x_h + 4  *$mp, 20 *$mp, 12 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
imagecopy($preview, $skin, $mp_x_h + 8  *$mp, 20 *$mp, 12 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
imagecopy($preview, $skin, $mp_x_h + 4  *$mp, 0  *$mp, 56 *$mp, 8  *$mp, 8 *$mp, 8  *$mp);

if ($way_cloak)
imagecopyresized($preview, $cloak, $mp_x_h + 3*$mp, 8 *$mp, 1 *$mp_cloak, 1 *$mp_cloak, 10 *$mp, 16 *$mp, 10 *$mp_cloak, 16 *$mp_cloak);

$fullsize = imagecreatetruecolor(224, 224);

imagesavealpha($fullsize, true);
$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
imagefill($fullsize, 0, 0, $transparent);

imagecopyresized($fullsize, $preview, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($preview), imagesy($preview));

imagedestroy($preview);
imagedestroy($skin);
if ($way_cloak) imagedestroy($cloak);

return $fullsize;
}

$mode = 1;

$male  = (!empty($_GET['female']))? false : true;

$image     = false;

$tmp_id    = false;
$tmp_name  = false;
$tmp_user  = false;

$user_name = false;

$use_default_skin = false;

$way_skin   = '';
$way_cloak  = '';
	
    if ( isset($_GET["mini"])		) { $tmp_id = (int)$_GET['mini']; $mode = 2; }
elseif ( isset($_GET["user_id"])	)   $tmp_id = (int)$_GET['user_id']; // mode = 1
elseif ( isset($_GET["user_name"])	)   $tmp_name = $_GET['user_name']; 
else $use_default_skin = true; 

if ($tmp_id == -1) { $tmp_id = false; $use_default_skin = true; }

if ( isset($_GET['female']) ) $use_default_skin = true; 

if ( $tmp_id or $tmp_name ) {

  if ( $tmp_id )  $tmp_user = new User($tmp_id,$bd_users['id']);
  else            $tmp_user = new User($tmp_name,$bd_users['login']);
  
  if ( $tmp_user ) {
  
	$use_default_skin = $tmp_user->defaultSkinTrigger();
	
	if ( !$tmp_user->id() ) exit;	
	
	$user_name = $tmp_user->name();
	
    if ($tmp_user->isFemale()) $male = false;

	unset($tmp_user);	
  }
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

if ( $mode == 1 ) {

    /* Изображение не кешировано, создаем его в реальном времени */

	if (!file_exists($way_buffer)) {

		$skin = imagecreatefrompng($way_skin);		

		$r_skin  = ratio( $way_skin );
		$r_cloak = ($way_cloak)? ratio($way_cloak, 22, 1.29) : false;
		
		$image = create_skin($way_skin,$way_cloak,$r_skin,$r_cloak); 

    /* Читаем кеш из файла */	

	} else readfile($way_buffer);	    
	
} else { 

	if (!file_exists($way_buffer_mini)) {		
		
		$r_skin = ratio($way_skin);

		$image = mini($way_skin,$r_skin,151); 
	
	} else readfile($way_buffer_mini);
}
	
if ($image) {

    imagepng($image);
	
	if ($config['sbuffer']) {
		if ($mode == 1) imagepng($image,$way_buffer);
		else            imagepng($image,$way_buffer_mini);
	}
	
	imagedestroy($image);
}
?>