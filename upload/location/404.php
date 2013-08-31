<?php
if (!defined('MCR')) exit;

$page = 'Страница не найдена';
$sub_dir = '';

if (isset($_GET['route']) and strpos($_GET['route'], $site_ways['mcraft']) !== false) 

	$sub_dir = 'launcher/';

$content_main = View::ShowStaticPage('404.html', $sub_dir );
if ($sub_dir) exit($content_main); 