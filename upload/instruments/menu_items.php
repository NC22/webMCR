<?php 
if (!defined('MCR')) exit;

$menu_items = array (
	
	'main' => array (
	
		'name'			=> 'Главная',
		'url' 			=> '',
		'parent_id'		=> -1,
		'lvl'			=> -1,
		'permission'	=> -1,
		'active'		=> false,
		'inner_html'	=> '',
	),
	
	'control' => array (
	
		'name'			=> 'Управление',
		'url' 			=> '',
		'parent_id'		=> -1,
		'lvl'			=> 15,
		'permission'	=> -1,
		'active'		=> false,
		'inner_html'	=> '',
	),	
	
	'admin' => array (
	
		'name'			=> 'Администрирование',
		'url' 			=> ($config['rewrite'])? 'go/control' : '?mode=control',
		'parent_id'		=> 'control',
		'lvl'			=> 15,
		'permission'	=> -1,
		'active'		=> false,
		'inner_html'	=> '',
	),	

	'add_news' => array (
	
		'name'			=> 'Добавить новость',
		'url' 			=> ($config['rewrite'])? 'go/news_add' : '?mode=news_add',
		'parent_id'		=> 'control',
		'lvl'			=> 1,
		'permission'	=> 'add_news',
		'active'		=> false,
		'inner_html'	=> '',
	),

	'options' => array (
	
		'name'			=> 'Настройки',
		'url' 			=> ($config['rewrite'])? 'go/options' : '?mode=options',
		'parent_id'		=> -1,
		'lvl'			=> 1,
		'permission'	=> -1,
		'active'		=> false,
		'inner_html'	=> '',
	),	

	'exit' => array (
	
		'name'			=> 'Выход',
		'url' 			=> 'login.php?out=1',
		'parent_id'		=> -1,
		'lvl'			=> 1,
		'permission'	=> -1,
		'active'		=> false,
		'inner_html'	=> '',
	),	
);