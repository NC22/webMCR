<?php
if (!defined('FEEDBACK')) exit;

$item_id = 0;
$page    = 'Главная страница - Новости';	

if ( isset($_GET['id']) ) $item_id = (int) $_GET['id'];

$curlist = (isset($_GET['l']))? (int) $_GET['l'] : false;

require_once(MCR_ROOT.'instruments/catalog.class.php');
$news_manager = new NewsMenager(2, MCR_STYLE.'news/','index.php?id='.$item_id.'&amp;');    
    
    $content_main  = $news_manager->ShowFullById($item_id,$curlist);
	$content_main .= $news_manager->ShowCommentForm($item_id); 
	   	   
$menu->SetItemActive($menu_items['main']);
?>