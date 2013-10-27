<?php
if (!defined('FEEDBACK')) exit;

$item_id = 0;
$page    = 'Главная страница - Новости';	

if ( isset($_GET['id']) ) $item_id = (int) $_GET['id'];

$curlist = (isset($_GET['l']))? (int) $_GET['l'] : false;

loadTool('catalog.class.php');

$news_item = new News_Item($item_id, 'news/');    
    
$content_main = $news_item->ShowFull($curlist);
	   	   
$menu->SetItemActive('main');
?>