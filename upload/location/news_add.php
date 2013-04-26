<?php
if (!defined('FEEDBACK') or empty($user)) exit;
if (!$user->getPermission('add_news')) exit;

require(MCR_ROOT.'instruments/upload.class.php');
require(MCR_ROOT.'instruments/catalog.class.php');

$page = 'Добавить новость';    

$news_manager = new NewsMenager(null, MCR_STYLE.'news/');
$files_manager = new FileMenager();

$menu->SetItemActive('add_news');
$content_main = $news_manager->ShowNewsEditor();

// TODO вывод последних добавленых файлов $files_manager->ShowFilesByUser($list = 1, $user_id = false); 
$content_main .= $files_manager->ShowAddForm();