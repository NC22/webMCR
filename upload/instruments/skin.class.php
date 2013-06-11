<?php
/* WEB-APP : WebMCR (С) 2013 NC22 | License : GPLv3 */

if (!defined('MCR')) exit;

class skinGenerator2D {

/* Valid Skin proportions */

const SKIN_BASE = 64;
const SKIN_PROP = 2; // 64 / 2 

/* Valid Cloak proportions */

const CLOAK_BASE = 22;
const CLOAK_PROP = 1.29; // 22 / 17

	private static function ratio($file, $baze = 64, $prop = 2) {

	$input_size = @getimagesize($file);

	if (empty($input_size)) return false;

	if (round($input_size[0] / $input_size[1], 2) != round($prop,2)) return false;
	else if ($input_size[0] < $baze) return false;

	$mp = $input_size[0] / $baze;

	return $mp;
	}

	private static function imageflip(&$result, &$img, $rx = 0, $ry = 0, $x = 0, $y = 0, $size_x = null, $size_y = null) {
	
	if ($size_x  < 1) $size_x = imagesx($img);
	if ($size_y  < 1) $size_y = imagesy($img);
	 
		imagecopyresampled($result, $img, $rx, $ry, ($x + $size_x-1), $y, $size_x, $size_y, 0-$size_x, $size_y);
	}
	
	private static function half_x_image( $image, $side = 1 ) {

	 $size_x = round( imagesx( $image ) / 2 ); $size_y = imagesy( $image );
	 
	 $x_add = 0; if ( $side == 2 ) $x_add = $size_x;
	 
	 $new_image = imagecreatetruecolor($size_x, $size_y);
	 imagesavealpha($new_image, true);
	 imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 255, 255, 255, 127)); 
	 imagecopy($new_image, $image, 0, 0, 0 + $x_add, 0, $size_x, $size_y);

	 return $new_image;
	}
	
	public static function createHead($way_skin, $size = 151) {

	if (!file_exists($way_skin) or !$mp = self::ratio($way_skin, self::SKIN_BASE, self::SKIN_PROP)) return false;

		 $im = @imagecreatefrompng($way_skin);
	if (!$im)  return false; 

	$av = imagecreatetruecolor($size, $size);

	imagecopyresized($av, $im, 0, 0, 8 *$mp, 8 *$mp, $size, $size, 8 *$mp, 8 *$mp); 
	imagecopyresized($av, $im, 0, 0, 40*$mp, 8 *$mp, $size, $size, 8 *$mp, 8 *$mp);  

	imagedestroy($im);

	return $av;
	}
	
	public static function createPreview($way_skin, $way_cloak = false, $size = 224) {
	
	if (!file_exists($way_skin) or !$mp = self::ratio($way_skin, self::SKIN_BASE, self::SKIN_PROP) ) 
	
	return false;
	
	if (!$way_cloak or !file_exists($way_cloak) or !$mp_cloak = self::ratio($way_cloak, self::CLOAK_BASE, self::CLOAK_PROP)) 
	
	$way_cloak = false;
	
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
	self::imageflip($preview, $skin, 12*$mp, 8  *$mp, 44 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
	imagecopy($preview, $skin, 4 *$mp, 8  *$mp, 20 *$mp, 20 *$mp, 8 *$mp, 12 *$mp);
	imagecopy($preview, $skin, 4 *$mp, 20 *$mp, 4  *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
	self::imageflip($preview, $skin, 8 *$mp, 20 *$mp, 4  *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
	imagecopy($preview, $skin, 4 *$mp, 0  *$mp, 40 *$mp, 8  *$mp, 8 *$mp, 8  *$mp);

	imagecopy($preview, $skin, $mp_x_h + 4  *$mp, 8  *$mp, 32 *$mp, 20 *$mp, 8 *$mp, 12 *$mp);
	imagecopy($preview, $skin, $mp_x_h + 4  *$mp, 0  *$mp, 24 *$mp, 8  *$mp, 8 *$mp, 8  *$mp);
	self::imageflip($preview, $skin, $mp_x_h + 0  *$mp, 8  *$mp, 52 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
	imagecopy($preview, $skin, $mp_x_h + 12 *$mp, 8  *$mp, 52 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
	self::imageflip($preview, $skin, $mp_x_h + 4  *$mp, 20 *$mp, 12 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
	imagecopy($preview, $skin, $mp_x_h + 8  *$mp, 20 *$mp, 12 *$mp, 20 *$mp, 4 *$mp, 12 *$mp);
	imagecopy($preview, $skin, $mp_x_h + 4  *$mp, 0  *$mp, 56 *$mp, 8  *$mp, 8 *$mp, 8  *$mp);

	if ($way_cloak)
	imagecopyresized($preview, $cloak, $mp_x_h + 3*$mp, 8 *$mp, 1 *$mp_cloak, 1 *$mp_cloak, 10 *$mp, 16 *$mp, 10 *$mp_cloak, 16 *$mp_cloak);

	$fullsize = imagecreatetruecolor($size, $size);

	imagesavealpha($fullsize, true);
	$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
	imagefill($fullsize, 0, 0, $transparent);

	imagecopyresized($fullsize, $preview, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($preview), imagesy($preview));

	imagedestroy($preview);
	imagedestroy($skin);
	if ($way_cloak) imagedestroy($cloak);

	return $fullsize;
	}
	
	public static function savePreview($way_save, $way_skin, $way_cloak = false, $side = false, $size = 224) {
	
	if (file_exists($way_save)) unlink($way_save); 
	
	$new_skin = self::createPreview($way_skin, $way_cloak, $size);
	if (!$new_skin) return false;
	
	if ($side) $new_skin = half_x_image( $new_skin, $side);
		
	if (!$new_skin) return false;
		
	imagepng($new_skin, $way_save); 
	return $new_skin; 	
	}	
	
	public static function saveHead($way_save, $way_skin, $size = 151) {
	
	if (file_exists($way_save)) unlink($way_save); 
	
	$new_head = self::createHead($way_skin, $size);
	if (!$new_head) return false;
		
	imagepng($new_head, $way_save); 
	return $new_head; 	
	}

	public static function isValidSkin($way_skin) {

	if (!file_exists($way_skin)) return false; 
	
	$ratio = self::ratio($way_skin, self::SKIN_BASE, self::SKIN_PROP);

	if (!$ratio) return false;

	return $ratio; 	
	}	
	
	public static function isValidCloak($way_cloak) {
	
	if (!file_exists($way_cloak)) return false; 
	
	$ratio = self::ratio($way_cloak, self::CLOAK_BASE, self::CLOAK_PROP);
	if (!$ratio) return false;

	return $ratio; 	
	}	
}