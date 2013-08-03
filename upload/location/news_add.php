<?php
if (!defined('MCR')) exit;
if (empty($user) or !$user->getPermission('add_news')) { header("Location: ".BASE_URL); exit; }

loadTool('upload.class.php');
loadTool('catalog.class.php');

$page = 'Добавить новость';    

LoadTinyMCE();

$news_manager = new NewsManager(null, 'news/');
$files_manager = new FileManager('other/');

$menu->SetItemActive('add_news');
$content_main = $news_manager->ShowNewsEditor();

// TODO вывод последних добавленых файлов $files_manager->ShowFilesByUser($list = 1, $user_id = false); 
$content_main .= $files_manager->ShowAddForm();