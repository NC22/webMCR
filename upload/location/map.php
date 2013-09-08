<?php 
if (!defined('MCR')) exit;

$page = 'Карта'; 

$content_main = View::ShowStaticPage('map.html');

$menu->SetItemActive('map');
?>