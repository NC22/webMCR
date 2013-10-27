<?php header ("Content-type: image/png");
require('./system.php');

function ShowSkinWithBuff() { // auto generate default skin way if not enough params
global $uInfo, $config; 

	$skin = GetVisual('skin');
	if ($skin === false and $uInfo['name'] !== false ) return; // user not exists - all users have default skin in skins dir
	
	loadTool('skin.class.php');	
	$dir = MCRAFT . ( ( $uInfo['female'] == -1 and $uInfo['name'] ) ? 'tmp/skin_buffer/' : 'tmp/skin_buffer/default/' );
	$buffer = $dir.($uInfo['name'] ? $uInfo['name'] : 'Char').($uInfo['mini'] ? '_Mini' : '').($uInfo['female'] == 1 ? '_female' : '').'.png';

		if (file_exists($buffer)) { readfile($buffer); return; }
	elseif ( $config['sbuffer'] ) 
	
		$image = ($uInfo['mini'])? skinGenerator2D::saveHead($buffer, $skin) : skinGenerator2D::savePreview($buffer, $skin, GetVisual('cloak'));
	else 	
		$image = (!$uInfo['mini'])? skinGenerator2D::createHead($skin) : skinGenerator2D::createPreview($skin, GetVisual('cloak'));

	if ($image) { imagepng($image); imagedestroy($image); } 
}

function GetVisual($type = 'skin') {
global $site_ways, $uInfo;
	
	if ( $uInfo['name'] === false and $type == 'cloak' ) return false; // default cloak not supported
	$dir = MCRAFT . ( ( $uInfo['female'] == -1 and $uInfo['name'] ) ? $site_ways[$type.'s'] : 'tmp/default_skins/' );
	$way =  $dir . ($uInfo['name'] ? $uInfo['name'] : 'Char') . ($uInfo['female'] == 1 ? '_female' : '').'.png';
	return (file_exists($way)) ? $way : false;	
}

$uInfo = array (	'mini'		=> (isset($_GET['mini']) or isset($_GET['m'])) ? true : false,
					'female' 	=> !empty($_GET['female']) ? 1 : (isset($_GET['female']) ? 0 : -1),
					'name' 		=> !empty($_GET['user_name']) ? $_GET['user_name'] : false,
					'id'		=> (!empty($_GET['user_id']) or !empty($_GET['mini'])) ? (int) (empty($_GET['user_id']) ? $_GET['mini'] : $_GET['user_id']) : false );

if ( $uInfo['id'] ) {

	BDConnect('skin_viewer'); loadTool('user.class.php');	
	$tmp_user = new User($uInfo['id']) ;
	if ( !$tmp_user->id() ) exit; 

	$uInfo['name']		= ($tmp_user->defaultSkinTrigger()) ? '' : $tmp_user->name();	
}
ShowSkinWithBuff();