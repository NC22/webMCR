<?php
header('Content-Type: text/html; charset=UTF-8');

require_once('./system.php');
BDConnect('index');

require(MCR_ROOT.'instruments/user.class.php');
MCRAuth::userLoad();

function GetRandomAdvice() { return ($quotes = @file(MCR_STYLE.'sovet.txt'))? $quotes[rand(0, sizeof($quotes)-1)] : "Советов нет"; }

$menu = new Menu();

if ($config['offline'] and (empty($user) or $user->group() != 3)) exit(Menager::ShowStaticPage(MCR_STYLE.'site_closed.html'));

if (!empty($user)) {

$player       = $user->name();
$player_id    = $user->id();
$player_lvl   = $user->lvl();
$player_email = $user->email(); if (empty($player_email)) $player_email = 'Отсутствует'; 
$player_group = $user->getGroupName();
$player_money = $user->getMoney();
}

$content_main = ''; $content_side = ''; $addition_events = ''; $content_advice = GetRandomAdvice(); $mode = $config['s_dpage'];

	if (isset($_GET['id'])) $mode = 'news_full'; 
elseif (isset($_GET['mode'])) $mode = $_GET['mode']; 
elseif (isset($_POST['mode'])) $mode = $_POST['mode']; 

if ($mode == 'side') $mode = $config['s_dpage'];

switch ($mode) {
    case 'start': $page = 'Начать игру'; $content_main = Menager::ShowStaticPage(MCR_STYLE.'start-game.html');  break;
	case '404':   $page = 'Страница не найдена'; $content_main = Menager::ShowStaticPage(MCR_STYLE.'404.html'); break;
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

$content_menu = $menu->Show();

$servManager = new ServerMenager();
$content_servers = $servManager->Show('side');

unset($servManager);

include MCR_STYLE.'index.html';
?>