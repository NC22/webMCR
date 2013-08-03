<?php
if (!defined('FEEDBACK')) exit;

$item_id = 0;
$page    = 'Главная страница - Новости';	

if ( isset($_GET['id']) ) $item_id = (int) $_GET['id'];

$curlist = (isset($_GET['l']))? (int) $_GET['l'] : false;

loadTool('catalog.class.php');

$news_manager = new NewsManager(2, 'news/','index.php?id='.$item_id.'&amp;');    
    
    $content_main  = $news_manager->ShowFullById($item_id,$curlist);
	$content_main .= $news_manager->ShowCommentForm($item_id); 
	   	   
$menu->SetItemActive('main');
?>