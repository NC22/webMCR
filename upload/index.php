<?php /* WEB-APP : WebMCR (С) 2013 NC22 | License : GPLv3 */

header('Content-Type: text/html; charset=UTF-8');

require_once('./system.php');
BDConnect('index');

loadTool('user.class.php');
MCRAuth::userLoad();

function GetRandomAdvice() { return ($quotes = @file(View::Get('sovet.txt')))? $quotes[rand(0, sizeof($quotes)-1)] : "Советов нет"; }

function LoadTinyMCE() {
global $addition_events, $content_js;
 
	if ( !file_exists(MCR_ROOT.'instruments/tiny_mce/tinymce.min.js') ) return false;

	$tmce  = 'tinymce.init({';
	$tmce .= 'selector: "textarea.tinymce",';
	$tmce .= 'language : "ru",';
	$tmce .= 'plugins: "code preview image link",';
	$tmce .= 'toolbar: "undo redo | bold italic | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | link image | preview",';
	$tmce .= '});';

	$addition_events .= $tmce;
	$content_js .= '<script type="text/javascript" src="instruments/tiny_mce/tinymce.min.js"></script>';
	
	return true;
}

function InitJS () {
global $addition_events;
	
	$init_js  = "var pbm; var way_style = '".DEF_STYLE_URL."'; var cur_style = '".View::GetURL()."'; var base_url  = '".BASE_URL."';" ;
	$init_js .= "window.onload = function () { mcr_init(); ". $addition_events ." } " ; 
	return '<script type="text/javascript">' . $init_js . '</script>' ;
}

$menu = new Menu();

if ($config['offline'] and (empty($user) or $user->group() != 3)) exit(View::ShowStaticPage('site_closed.html'));

$content_main = ''; $content_side = ''; $addition_events = ''; $content_js = ''; $content_advice = GetRandomAdvice();

if (!empty($user)) {

$player       = $user->name();
$player_id    = $user->id();
$player_lvl   = $user->lvl();
$player_email = $user->email(); if (empty($player_email)) $player_email = lng('NOT_SET'); 
$player_group = $user->getGroupName();
$player_money = $user->getMoney();

	if ($user->group() == 4) $content_main .= View::ShowStaticPage('profile_verification.html', 'profile/', $player_email);
}

$mode = $config['s_dpage'];

	if (isset($_GET['id'])) $mode = 'news_full'; 
elseif (isset($_GET['mode'])) $mode = $_GET['mode']; 
elseif (isset($_POST['mode'])) $mode = $_POST['mode']; 

if ($mode == 'side') $mode = $config['s_dpage'];

switch ($mode) {
    case 'start': $page = 'Начать игру'; $content_main = View::ShowStaticPage('start_game.html');  break;
	case 'register': 
	case 'news':	  include('./location/news.php');		break;
	case 'news_full': include('./location/news_full.php');	break;
    case 'options':   include('./location/options.php');	break;
	case 'news_add':  include('./location/news_add.php');	break;
    case 'control':   include('./location/admin.php');		break; 
    default: 
		if (!preg_match("/^[a-zA-Z0-9_-]+$/", $mode) or !file_exists(MCR_ROOT.'/location/'.$mode.'.php')) $mode = $config['s_dpage']; 

		include(MCR_ROOT.'/location/'.$mode.'.php'); break;
} 

include('./location/side.php'); 

$content_menu = $menu->Show(); $content_js .= InitJS();

include View::Get('index.html');
?>