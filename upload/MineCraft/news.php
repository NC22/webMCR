<?php
header('Content-Type: text/html;charset=UTF-8');

require('../system.php');

loadTool('user.class.php'); 
loadTool('monitoring.class.php');
loadTool('catalog.class.php');

DBinit('news');

$news = '';
$page_title = 'Новостная лента';

$news_manager = new NewsManager($config['game_news'], 'launcher/news/', $config['s_root'].'index.php?');

$curlist = Filter::input('l', 'get', 'int');
if ($curlist <= 0) $curlist = 1;

$news = $news_manager->ShowNewsListing($curlist);

$servManager = new ServerManager('launcher/serverstate/');
$server_state_html = $servManager->Show('game');
unset($servManager);
			  
include View::Get('index.html', 'launcher/');
