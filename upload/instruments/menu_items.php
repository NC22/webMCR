<?php if (!defined('MCR')) exit;
$menu_items = array (
  0 => 
  array (
    'main' => 
    array (
      'name' => 'Главная',
      'url' => '',
      'parent_id' => -1,
      'lvl' => -1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'guide' => 
    array (
      'name' => 'Начать играть',
      'url' => 'go/guide/',
      'parent_id' => -1,
      'lvl' => -1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'rules' => 
    array (
      'name' => 'Правила',
      'url' => 'go/rules/',
      'parent_id' => -1,
      'lvl' => -1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'options' => 
    array (
      'name' => 'Настройки',
      'url' => 'go/options/',
      'parent_id' => -1,
      'lvl' => 1,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
  ),
  1 => 
  array (
    'admin' => 
    array (
      'name' => 'Администрирование',
      'url' => '',
      'parent_id' => -1,
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'add_news' => 
    array (
      'name' => 'Добавить новость',
      'url' => 'go/news_add/',
      'parent_id' => 'admin',
      'lvl' => 1,
      'permission' => 'add_news',
      'active' => false,
      'inner_html' => '',
    ),
    'control' => 
    array (
      'name' => 'Пользователи',
      'url' => 'control/user/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'category_news' => 
    array (
      'name' => 'Категории новостей',
      'url' => 'control/category/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'reg_edit' => 
    array (
      'name' => 'Регистрация',
      'url' => 'control/ipbans/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'group_edit' => 
    array (
      'name' => 'Группы',
      'url' => 'control/group/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'file_edit' => 
    array (
      'name' => 'Файлы',
      'url' => 'control/filelist/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'site_edit' => 
    array (
      'name' => 'Сайт',
      'url' => 'control/constants/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'rcon' => 
    array (
      'name' => 'RCON',
      'url' => 'control/rcon/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'game_edit' => 
    array (
      'name' => 'Настройки игры',
      'url' => 'control/update/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'serv_edit' => 
    array (
      'name' => 'Мониторинг серверов',
      'url' => 'control/servers/',
      'parent_id' => 'admin',
      'lvl' => 15,
      'permission' => -1,
      'active' => false,
      'inner_html' => '',
    ),
    'exit' => 
    array (
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
