<?php
header('Content-Type: text/html; charset=UTF-8');

require_once('./system.php');
BDConnect();

require(MCR_ROOT.'instruments/user.class.php');
MCRAuth::userLoad();

function GetRandomAdvice() { return ($quotes = @file(MCR_STYLE.'sovet.txt'))? $quotes[rand(0, sizeof($quotes)-1)] : "Советов нет"; }

$menu = new Menu();
$menu_items['main'] = $menu->AddItem('Главная','',true);

if (!empty($user)) {

  if ($user->getPermission('add_news')) $menu_items['add_news'] = $menu->AddItem('Добавить новость', ($config['rewrite'])? 'go/news_add' : '?mode=news_add');
  if ($user->lvl() >= 15)               $menu_items['admin']    = $menu->AddItem('Управление', ($config['rewrite'])? 'go/control' : '?mode=control');
  if ($user->lvl() > 0)                 $menu_items['options']  = $menu->AddItem('Настройки', ($config['rewrite'])? 'go/options' : '?mode=options');
}

if (!empty($user)) {

$player       = $user->name();
$player_id    = $user->id();
$player_lvl   = $user->lvl();
$player_email = $user->email(); if (empty($player_email)) $player_email = 'Отсутствует'; 
$player_group = $user->getGroupName();
$player_money = $user->getMoney();
}

$content_main = ''; $content_side = ''; $addition_events = ''; $content_advice = GetRandomAdvice(); $mode = null;

if ( isset($_GET['id']) ) $mode = 'news_full';
else $mode = (empty($_GET['mode']) or (empty($user) and in_array($_GET['mode'], array('options', 'news_add', 'control'))))? $config['s_dpage'] : $_GET["mode"]; 

switch ($mode) {
    case 'start': $page = 'Начать игру'; $content_main = Menager::ShowStaticPage(STYLE_URL.'start-game.html');  break;
	case '404':   $page = 'Страница не найдена'; $content_main = Menager::ShowStaticPage(STYLE_URL.'404.html'); break;
	case 'register': 
	case 'news':	  include('./location/news.php');		break;
	case 'news_full': include('./location/news_full.php');	break;
    case 'options':   include('./location/options.php');	break;
	case 'news_add':  include('./location/news_add.php');	break;
    case 'control':   include('./location/admin.php');		break; 
    default: 
		if (!preg_match("/^[a-zA-Z0-9_-]+$/", $mode) or !file_exists(MCR_ROOT.'/location/'.$mode.'.php')) $mode = $config['s_dpage']; 

		include(MCR_ROOT.'/location/'.$mode.'.php');  	
	break;
} 

include('./location/side.php'); 

$menu_items['exit'] = $menu->AddItem('Выход','login.php?out=1');
$content_menu 		= $menu->Show();

$servManager = new ServerMenager();
$content_servers 	= $servManager->Show('side');

unset($servManager);

include MCR_STYLE.'index.html';
?>