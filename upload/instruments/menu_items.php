<?php if (!defined('MCR')) exit;

$menu_items = array (

  0 => array (
  
    'main' => array (
	
      'name' => 'Главная',
      'url' => '',
      'parent_id' => -1,
      'lvl' => -1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
	
    'control' => array (
	
      'name' => 'Управление',
      'url' => '',
      'parent_id' => -1,
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
	
    'admin' => array (
	
      'name' => 'Администрирование',
      'url' => 'go/control',
      'parent_id' => 'control',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
	
    'add_news' => array (
	
      'name' => 'Добавить новость',
      'url' => 'go/news_add',
      'parent_id' => 'control',
      'lvl' => 1,
      'permission' => 'add_news',
      'active' => false,
      'inner_html' => '',
    ),
	
    'help' => array (
	
      'name' => 'Помощь',
      'url' => '',
      'parent_id' => -1,
      'lvl' => -1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
	
    'guide' => array (
      'name' => 'Как начать играть',
      'url' => 'go/guide',
      'parent_id' => 'help',
      'lvl' => -1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
	
    'rules' => array (
	
      'name' => 'Правила',
      'url' => 'go/rules',
      'parent_id' => 'help',
      'lvl' => -1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
	
    'options' => array (
	
      'name' => 'Настройки',
      'url' => 'go/options',
      'parent_id' => -1,
      'lvl' => 1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
  ),
  
  1 => array (
  
    'exit' => array (
	
      'name' => 'Выход',
      'url' => 'login.php?out=1',
      'parent_id' => -1,
      'lvl' => 1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',	  
    ),
	
  ),
);
