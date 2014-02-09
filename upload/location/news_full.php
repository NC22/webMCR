<?php
if (!defined('FEEDBACK')) exit;

$page    = 'Главная страница - Новости';

$item_id = Filter::input ('id', 'get', 'int');
if ($item_id <= 0) exit;

$curlist = Filter::input('l', 'get', 'int');

loadTool('catalog.class.php');

$news_item = new News_Item($item_id, 'news/');    
    
$content_main = $news_item->ShowFull($curlist);
	   	   
$menu->SetItemActive('main');
