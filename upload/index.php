<?php
/*
    This file is part of webMCR.

    webMCR is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webMCR is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with webMCR.  If not, see <http://www.gnu.org/licenses/>.

 */

header('Content-Type: text/html; charset=UTF-8');

require_once('./system.php');
BDConnect('index');

require(MCR_ROOT.'instruments/user.class.php');
MCRAuth::userLoad();

function GetRandomAdvice() { return ($quotes = @file(MCR_STYLE.'sovet.txt'))? $quotes[rand(0, sizeof($quotes)-1)] : "Советов нет"; }

function LoadTinyMCE() {
global $addition_events, $content_js;
 
	if (!file_exists(MCR_ROOT.'instruments/tinymce/tinymce.min.js') ) return false;

	$tmce = 'tinymce.init({';
	$tmce .= 'selector: "textarea.tinymce",';
	$tmce .= 'language : "ru",';
	$tmce .= 'plugins: "code preview image link",';
	$tmce .= 'toolbar: "undo redo | bold italic | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | link image | preview",';
	$tmce .= '});';

	$addition_events .= $tmce;
	$content_js .= '<script type="text/javascript" src="instruments/tinymce/tinymce.min.js"></script>';
	
	return true;
}

$menu = new Menu();

if ($config['offline'] and (empty($user) or $user->group() != 3)) exit(Menager::ShowStaticPage(MCR_STYLE.'site_closed.html'));

if (!empty($user)) {

$player       = $user->name();
$player_id    = $user->id();
$player_lvl   = $user->lvl();
$player_email = $user->email(); if (empty($player_email)) $player_email = lng('NOT_SET'); 
$player_group = $user->getGroupName();
$player_money = $user->getMoney();
}

$content_main = ''; $content_side = ''; $addition_events = ''; $content_advice = GetRandomAdvice(); $content_js = '';

$mode = $config['s_dpage'];

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