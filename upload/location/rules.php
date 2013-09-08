<?php 
if (!defined('MCR')) exit;

$page = 'Правила сервера'; 

$content_main = View::ShowStaticPage('rules.html');

$menu->SetItemActive('rules');
?>