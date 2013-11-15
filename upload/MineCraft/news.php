<?php
header('Content-Type: text/html;charset=UTF-8');

require('../system.php');

loadTool('user.class.php'); 
loadTool('monitoring.class.php');
loadTool('catalog.class.php');

BDConnect('news');

$news = '';
$page_title = 'Новостная лента';

$news_manager = new NewsManager($config['game_news'], 'launcher/news/', $config['s_root'].'index.php?');

if (isset($_GET['l'])) $curlist = (int) $_GET['l']; 
else                   $curlist = 1; 
	
if (isset($_GET['id'])) $spec_new = (int) $_GET['id']; 
else                    $spec_new = -1; 

$news = $news_manager->ShowNewsListing($curlist);

$servManager = new ServerManager('launcher/serverstate/');
$server_state_html = $servManager->Show('game');
unset($servManager);
			  
include View::Get('index.html', 'launcher/');
?>