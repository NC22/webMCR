<?php
Header("Content-Type: image/png");

if (!isset($_SESSION)) session_start();

$code = rand(1000,9999);

$_SESSION['code'] = $code;

mcraftcha($code);

function mcraftcha($code) {

$colors = array ( 0 => array( 0 => array( 0 => 82, 1 => 149, 2 => 47), //normal
							  1 => array( 0 => 64, 1 => 116, 2 => 37),
							  2 => array( 0 => 58, 1 => 106, 2 => 34),
							  3 => array( 0 => 53, 1 => 97,  2 => 31) ),
							  
			      1 => array( 0 => array( 0 => 67, 1 => 146, 2 => 42), //tropical
							  1 => array( 0 => 51, 1 => 112, 2 => 32),
							  2 => array( 0 => 44, 1 => 95, 2 => 27),
							  3 => array( 0 => 31, 1 => 69, 2 => 20) ),
							  
			      2 => array( 0 => array( 0 => 123, 1 => 121, 2 => 60), //savanna
							  1 => array( 0 => 104, 1 => 103, 2 => 51),
							  2 => array( 0 => 94, 1 => 93, 2 => 46),
							  3 => array( 0 => 74, 1 => 73, 2 => 36)),
							  
			      3 => array( 0 => array( 0 => 110, 1 => 141, 2 => 86), //tundra
							  1 => array( 0 => 94, 1 => 121, 2 => 73),
							  2 => array( 0 => 78, 1 => 100, 2 => 61),
							  3 => array( 0 => 58, 1 => 74, 2 => 45) ),							  
							  
			      4 => array( 0 => array( 0 => 194, 1 => 227, 2 => 167), //text colors
							  1 => array( 0 => 194, 1 => 227, 2 => 167),
							  2 => array( 0 => 203, 1 => 234, 2 => 179),
							  3 => array( 0 => 217, 1 => 241, 2 => 205) ) );
							  
$image = imagecreatetruecolor(10, 4);
$mainColor = rand(0,3);

  for($x = 0; $x < 10; $x++ )  
  
    for($y = 0; $y < 4; $y++ ) {

	$color = $colors[$mainColor][rand(0,3)];
	
      imagesetpixel($image, $x, $y,
        imagecolorallocate($image, $color[0], $color[1], $color[2]) );

    }  

$full = imagecreatetruecolor(100,40);
imagecopyresized($full,$image, 0, 0, 0, 0, imagesx($full), imagesy($full), imagesx($image), imagesy($image)); 

$textColor = $colors[4][$mainColor];
imagettftext($full, 22, rand(-4,4), rand(4,10), rand(32,36), imagecolorallocate($full, $textColor[0], $textColor[1], $textColor[2]), './mcraft.ttf', $code);

imagedestroy($image);
imagepng($full);
imagedestroy($full);
//return $full;
}
?>