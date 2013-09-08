<?php
if (!defined('MCR')) exit;

$page = 'Как начать играть'; 

$content_main = View::ShowStaticPage('guide.html');

$menu->SetItemActive('guide');
?>