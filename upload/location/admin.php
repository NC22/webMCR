<?php
if (!defined('MCR')) exit;
 
if (empty($user) or $user->lvl() < 15) exit;

require(MCR_ROOT.'instruments/catalog.class.php');
require(MCR_ROOT.'instruments/alist.class.php');
require(MCR_ROOT.'instruments/monitoring.class.php');
 
function RatioList($selectid = 1) {

$html_ratio = '<option value="1" '.((1 == $selectid)?'selected':'').'>64x32 | 22x17</option>';

	for ($i=2;$i<=32;$i=$i+2)
		$html_ratio .= '<option value="'.$i.'" '.(($i == $selectid)?'selected':'').'>'.(64*$i).'x'.(32*$i).' | '.(22*$i).'x'.(17*$i).'</option>';
		
return $html_ratio;
}

function SaveOptions() {
global $config,$bd_names,$bd_money,$bd_users,$site_ways,$info;

$txt  = '<?php'.PHP_EOL;
$txt .= '$config = '.var_export($config, true).';'.PHP_EOL;
$txt .= '$bd_names = '.var_export($bd_names, true).';'.PHP_EOL;
$txt .= '$bd_users = '.var_export($bd_users, true).';'.PHP_EOL;
$txt .= '/* iconomy or some other plugin, just check names */'.PHP_EOL;
$txt .= '$bd_money = '.var_export($bd_money, true).';'.PHP_EOL;
$txt .= '$site_ways = '.var_export($site_ways, true).';'.PHP_EOL;
$txt .= '/* Put all new config additions here */'.PHP_EOL;
$txt .= '?>';

$result = file_put_contents("config.php", $txt);

	if (is_bool($result) and $result == false) {

	$info .= 'Файл '.MCR_ROOT.'config.php не существует \ защищен от записи \ папка содержащая файл не доступна для записи. Настройки не были сохранены.';	
	return false;
	}

return true;
}

$menu->SetItemActive($menu_items['admin']);

/* Default vars */
$page    = 'Панель управления';

$curlist = (isset($_GET['l']))? (int) $_GET['l'] : 1;
$do      = (isset($_GET['do']))? $_GET['do'] : 'all'; 

$html = ''; $info = ''; $server_info = '';

$user_id = (!empty($_POST['user_id']))? (int)$_POST['user_id'] : false;
$user_id = (!empty($_GET['user_id']))? (int)$_GET['user_id'] : $user_id;
$ban_user = new User($user_id,$bd_users['id']);

if ($ban_user->id()) { 

	$user_name = $ban_user->name();
	$user_gen  = $ban_user->isFemale();
	$user_mail = $ban_user->email();
	$user_id   = $ban_user->id();
	$user_ip   = $ban_user->ip();
	$user_lvl  = $ban_user->lvl();
	
} else $ban_user = false;

if ( !extension_loaded('gd') )      $html .= 'Библиотека GD не подключена, пользователь не сможет увидеть загруженый скин \ плащ в профиле.<br/>';
if ( ini_get('register_globals') )  $html .= '[<span style="color: #b02900;">Нарушение безопасности</span>] [ Файл php.ini настроек PHP ] [Опция] <b>register_globals = On</b>. Привести в значение <b>Off</b><br/>';
if ( !function_exists('fsockopen')) $html .= 'Функция fsockopen недоступна. Подключиться и проверить состояние игрового сервера будет невозможно.<br/>';

	if (!empty($_GET['sid'])) $id = (int)$_GET['sid']; 
	else $id = false;
 

if ($do) {
// Buffer OFF 
 switch ($do) {
	case 'filelist':

	require(MCR_ROOT.'instruments/upload.class.php');	
	
	$url = 'index.php?mode=control&do=filelist';
	if ($user_id) $url .= '&user_id='.$user_id;
	
	$files_manager = new FileMenager(false, $url.'&');
	$content_main .= Menager::ShowStaticPage(MCR_STYLE.'admin/filelist_info.html');
	$content_main .= $files_manager->ShowAddForm();
	
	$html .= $files_manager->ShowFilesByUser($curlist, $user_id);	
	break;
	case 'log':
	$log_file = MCR_ROOT.'log.txt';
	
	if (!file_exists($log_file)) { $html .= '<b>'.$log_file.'</b><br> Файл отсутствует'; break; }
	
	$file = @file($log_file);
	$count = count($file);
	$max = 30;	
	$total = ceil($count/$max);
	
	if ( $curlist > $total) $curlist = $total;
	
	$first = $curlist*$max-$max;
	$last = $curlist*$max-1;
	
	$html .= '<b>'.$log_file.'</b><br>';
	
	for($i = $first;$i<=$last;$i++)
		if(@$file[$i]) $html .= $file[$i].'<br>';	
	
	$arrGen = new Menager();
	$html .= $arrGen->arrowsGenerator('index.php?mode=control&do=log&',$curlist,$count,$max,'other/common');
	
	break;
    case 'all':
	ob_start(); include MCR_STYLE.'admin/user_find.html'; 
	$html .= ob_get_clean();
	
    $controlMenager = new ControlMenager(false, 'index.php?mode=control&');
    $html .= $controlMenager->ShowUserListing($curlist, 'none');
	
	$do = false;	
	break;
    case 'search': 
	
	ob_start(); include MCR_STYLE.'admin/user_find.html'; 
	$html .= ob_get_clean();
	
	if ( !empty($_GET["sby"]) and 
	     !empty($_GET['input'])     and 
		( preg_match("/^[a-zA-Z0-9_-]+$/", $_GET['input']) or 
		  preg_match("/[0-9.]+$/", $_GET['input'])         or 
		  preg_match("/[0-9]+$/", $_GET['input']) )) {
		  
	$search_by = $_GET["sby"];
	$input     = $_GET['input'];
 
    $controlMenager = new ControlMenager(false, 'index.php?mode=control&do=search&sby='.$search_by.'&input='.$input.'&');
    $html .= $controlMenager->ShowUserListing($curlist, $search_by, $input);	
	}	
	
	$do = false;	
	break;
    case 'ipbans': 
		
    if (isset($_POST['timeout'])) {
	
	 if (isset($_POST['timeout']))
      sqlConfigSet('next-reg-time',(int)$_POST['timeout']);
	  
	  sqlConfigSet('email-verification',(isset($_POST['emailver']))? 1 : 0);
	  
	 $info .= 'Правила обновлены';
	 
    } elseif (  POSTGood('def_skin_male')  or POSTGood('def_skin_female')) {		

		$female = (POSTGood('def_skin_female'))? true : false;
		$tmp_dir = MCRAFT.'tmp/';
		
		$default_skin     = $tmp_dir.'default_skins/Char'.(($female)? '_female' : '').'.png';
		$default_skin_md5 = $tmp_dir.'default_skins/md5'.(($female)? '_female' : '').'.md5';		
        $way_buffer_mini  = $tmp_dir.'skin_buffer/default/Char_Mini'.(($female)? '_female' : '').'.png';
        $way_buffer       = $tmp_dir.'skin_buffer/default/Char'.(($female)? '_female' : '').'.png';  	
		
		$new_file_info = POSTSafeMove(($female)? 'def_skin_female' : 'def_skin_male', $tmp_dir);
		
		require_once(MCR_ROOT.'instruments/skin.class.php');
		if ($new_file_info and skinGenerator2D::isValidSkin($tmp_dir.$new_file_info['tmp_name']) and rename( $tmp_dir.$new_file_info['tmp_name'], $default_skin)) {
		
			chmod($default_skin, 0777);
			$info .= 'Скин ['.(($female)? 'Мальчик' : 'Девочка').'] изменен.<br/>';  
					
			if (file_exists($default_skin_md5) ) unlink($default_skin_md5);	
			if (file_exists($way_buffer_mini) )  unlink($way_buffer_mini);
			if (file_exists($way_buffer) )       unlink($way_buffer);
			
		} else $info .= 'Ошибка загрузки. Скин ['.(($female)? 'Мальчик' : 'Девочка').']<br/>';  
	}	
  
	$timeout = (int)sqlConfigGet('next-reg-time');
	$verification = ((int)sqlConfigGet('email-verification'))? true : false;
	
	ob_start(); include MCR_STYLE.'admin/timeout.html'; $html .= ob_get_clean();  
	
    $controlMenager = new ControlMenager(false, 'index.php?mode=control&do=ipbans&');
    $html .= $controlMenager->ShowIpBans($curlist);
	
	$do = false;	
	break;
    case 'servers': 
		
    $controlMenager = new ControlMenager(false, 'index.php?mode=control&do=servers&');
    $html .= $controlMenager->ShowServers($curlist);
	
	$do = false;	
	break;		
 }
}

if ($do) { 

// Buffer ON 

 ob_start();
 
  switch ($do) {
  
    case 'ban':	
	
	if (isset($_POST['confirm']) and $ban_user) {     
		$ban_user->changeGroup(2);			
		$info .= 'Аккаунт пользователя заблокирован';
	}
	
	if ($ban_user) include MCR_STYLE.'admin/user_ban.html'; 
	
	break;
	case 'banip':  
	if (isset($_POST['confirm']) and $ban_user and !empty($_POST['banip_days'])) { 	
  
    $ban_time     = (int)$_POST['banip_days'];
	$and_ban_user = (isset($_POST['banip_anduser']) and (int)$_POST['banip_anduser'])? true : false;
		
		BD("DELETE FROM {$bd_names['ip_banning']} WHERE IP='".TextBase::SQLSafe($ban_user->ip())."'");	
		BD("INSERT INTO {$bd_names['ip_banning']} (IP,time_start,ban_until) VALUES ('".TextBase::SQLSafe($ban_user->ip())."',NOW(),NOW()+INTERVAL ".TextBase::SQLSafe($ban_time)." DAY)");
		
		$info .= 'Регистрация на сайте заблокирована<br/>';
		
		if ($and_ban_user) {
			
			$ban_user->changeGroup(2);			
			$info .= 'Аккаунт пользователя заблокирован';
		} 
	}		
	if ($ban_user) include MCR_STYLE.'admin/user_ban_ip.html';    
	break;
	case 'delete':	
	if (isset($_POST['confirm']) and $ban_user) {     
	
		$ban_user->Delete();
		$html .= 'Аккаунт пользователя удален';
		unset($ban_user);
		
	} elseif ($ban_user) include MCR_STYLE.'admin/user_del.html';  
	
	break;
    case 'rcon': 
	
    $save = true;	
	$ip = sqlConfigGet('rcon-serv');
	if ($ip == 0) { $ip = ''; $save = false; }
	$port = sqlConfigGet('rcon-port');
	if ($port == 0) $port = '';
	
	include MCR_STYLE.'admin/rcon.html';   	
	break;
	case 'update':
	
		$new_build  = (!empty($_POST['build_set']))? (int)$_POST['build_set'] : false;
		$new_version_l = (!empty($_POST['launcher_set']))? (int)$_POST['launcher_set'] : false;
		
		$link_win  = (!empty($_POST['link_win']))? TextBase::HTMLDestruct($_POST['link_win']) : false;
		$link_lin  = (!empty($_POST['link_lin']))? TextBase::HTMLDestruct($_POST['link_lin']) : false;
		$game_news = (!empty($_POST['game_news']))? (int)$_POST['game_news'] : false;
		
		if ($link_win)  $config['s_llink_win'] = $link_win;
		if ($link_lin)  $config['s_llink_lin'] = $link_lin;
		if (!is_bool($game_news)) {
		
				if ($game_news <= 0) $config['game_news'] = 0;
			elseif (CategoryMenager::ExistByID($game_news)) $config['game_news'] = $game_news;
		}
		
		if ($link_win or $link_lin or $game_news) 
			if (SaveOptions()) $info .= 'Файл настроек обновлен.';
		
		if ($new_build) {			
				sqlConfigSet('latest-game-build',$new_build); $info .= 'Build изменен '.$new_build.'<br/>';
			}
			
		if ($new_version_l) {			
				sqlConfigSet('launcher-version',$new_version_l); $info .= 'Версия лаунчера изменена '.$new_version_l.'<br/>';
			}
					
        $game_lver  = sqlConfigGet('launcher-version');
        $game_build = sqlConfigGet('latest-game-build');
		$cat_list = '<option value="-1">Последние новости</option>';	
		$cat_list .= CategoryMenager::GetList($config['game_news']);	
		
		include MCR_STYLE.'admin/game.html';   		 
	break;
	case 'category': 
	
	if (!$id and isset($_POST['name']) and isset($_POST['lvl']) and isset($_POST['desc'])) {  
		$new_category = new Category();
		if ($new_category->Create($_POST['name'], $_POST['lvl'], $_POST['desc'])) $info .= 'Категория создана.';
		else  $info .= 'Категория с таким названием уже существует.';
		
	} elseif ($id and isset($_POST['edit']) and isset($_POST['name']) and isset($_POST['lvl']) and isset($_POST['desc'])) { 
	
		$category = new Category($id);
		if ($category->Edit($_POST['name'], $_POST['lvl'], $_POST['desc'])) $info .= 'Категория изменена.';
		else  $info .= 'Категория с таким названием уже существует.';
		
	} elseif ($id and isset($_POST['delete'])) {  
	
		$category = new Category($id);
		if ($category->Delete()) { 		
		       $info .= 'Категория удалена.';
		} else $info .= 'Категория не найдена.';
		
		$id = false;
	}
	
	$cat_list = CategoryMenager::GetList($id);	
	include MCR_STYLE.'admin/category_header.html';
	
	if ($id) {
		$cat_item = new Category($id);
		
		if ($cat_item->Exist()) {

		$cat_name      = $cat_item->GetName(); 
		$cat_desc      = $cat_item->GetDescription(); 
		$cat_priority  = $cat_item->GetPriority();
		
		include MCR_STYLE.'admin/category_edit.html'; 
		if (!$cat_item->IsSystem()) include MCR_STYLE.'admin/category_delete.html';
		} 
	unset($cat_item);					
	} else include MCR_STYLE.'admin/category_add.html';
	break; 				 
	case 'group':	
	
	// Пустое название группы
	
	if (!$id and isset($_POST['name'])) {  
		$new_group = new Group();
		if ($new_group->Create($_POST['name'], $_POST)) $info .= 'Группа создана.';
		else  $info .= 'Группа с таким названием уже существует.';
		
	} elseif ($id and isset($_POST['edit']) and isset($_POST['name'])) { 
	
		$new_group = new Group($id);
		if ($new_group->Edit($_POST['name'], $_POST)) $info .= 'Группа изменена.';
		else  $info .= 'Группа с таким названием уже существует.';
		
	} elseif ($id and isset($_POST['delete'])) {  
	
		$new_group = new Group($id);
		if ($new_group->Delete()) { 		
		       $info .= 'Группа удалена.';
		} else $info .= 'Группа не найдена.';
		
		$id = false;
	}
	
	$group_list = GroupMenager::GetList($id);	
	include MCR_STYLE.'admin/group_header.html';
	
	if ($id) {	 
	
		$group_i = new Group($id);		
		$group      = $group_i->GetAllPermissions();
		$html_ratio = RatioList($group['max_ratio']);
		$group_name = $group_i->GetName();
		
		include MCR_STYLE.'admin/group_edit.html'; 
        if (!$group_i->IsSystem()) include MCR_STYLE.'admin/group_delete.html';
		unset($group_i);		
	} else {

		$html_ratio = RatioList();
	    include MCR_STYLE.'admin/group_add.html';  
	}
	break;	
    case 'server_edit': 
	
    include MCR_STYLE.'admin/server_edit_header.html';  
	
	if (isset($_POST['address']) and isset($_POST['port']) and isset($_POST['method'])) {  
	    		 
		 $serv_address  = $_POST['address'];
		 
		 $serv_port     = (int)$_POST['port'];
		 $serv_method   = (int)$_POST['method']; 
		 
		 $serv_name     = (isset($_POST['name']))? $_POST['name'] : '';		 
		 $serv_info     = (isset($_POST['info']))? $_POST['info'] : '';	
		 
		 $serv_rcon     = (isset($_POST['rcon_pass']) and $serv_method == 2) ? $_POST['rcon_pass'] : false;
		 
		 if ($serv_method == 2 and !$serv_rcon) $serv_method = false;
		 
		 $serv_ref      = (isset($_POST['timeout']))? (int)$_POST['timeout'] : 5;	
		 $serv_priority = (isset($_POST['priority']))? (int)$_POST['priority'] : 0;
			
		 $serv_side     = (isset($_POST['main_page']))? true : false;
		 $serv_game     = (isset($_POST['game_page']))? true : false;
		 $serv_mon      = (isset($_POST['stat_page']))? true : false;	
		 
		if ($id) {
		    
			$server = new Server($id);
		
			if (!$server->Exist()) { $info .= 'Сервер не найден.'; break; }
			
			if ($serv_name)     $server->SetText($serv_name, 'name');
			if ($serv_info)     $server->SetText($serv_info, 'info');
			
			if ($serv_method)   $server->SetConnectMethod($serv_method, $serv_rcon);
			
			if ($serv_address and $serv_port) $server->SetConnectWay($serv_address, $serv_port);
			
			$info .= 'Данные сервера обновлены.';

		} else {
		
		  if (is_bool($serv_method)) { $info .= 'Пароль для выбранного типа подключения не указан.'; break; }
		  
		  $server = new Server();
		  
		  if ($server->Create($serv_address, $serv_port, $serv_method, $serv_rcon, $serv_name, $serv_info) == 1) $info .= 'Отслеживаемый сервер добавлен.';
		  else { $info .= 'Настройки подключения не выбраны.'; break; }
		  
		  $server->UpdateState(true);
		}
		 
		$server->SetPriority($serv_priority);
		$server->SetRefreshTime($serv_ref); 
		
		$server->SetVisible('side',$serv_side);
		$server->SetVisible('game',$serv_game);
		$server->SetVisible('mon',$serv_mon);
		
	} elseif ($id and isset($_POST['delete'])) {  
	
		$server = new Server($id);
		if ($server->Delete()) { 		
		       $info .= 'Сервер удален.';
		} else $info .= 'Сервер не найден.';
		
		$id = false;
	}
	
	if ($id) {	 
	    $server = new Server($id,MCR_STYLE.'admin/');
		
		$server->UpdateState(true);
        $server_info = $server->ShowHolder('mon','adm');	
		
		if (!$server->Exist()) { $info .= 'Сервер не найден.'; break; }
		
		$serv_name     = TextBase::HTMLDestruct($server->name());		
        $serv_method   = $server->method();	
		$serv_ref      = $server->refresh();	
		$serv_address  = $server->address();
		$serv_port     = $server->port();	
		$serv_info     = TextBase::HTMLDestruct($server->info());
		
		$serv_priority = $server->GetPriority();
		
        $serv_side     = $server->GetVisible('side');
		$serv_game     = $server->GetVisible('game');
		$serv_mon      = $server->GetVisible('mon');
		
		include MCR_STYLE.'admin/server_edit.html'; 

	} else include MCR_STYLE.'admin/server_add.html';  
    break;	
    case 'constants':  
	
	if (isset($_POST['site_name'])) {
	
	$site_name   = TextBase::HTMLDestruct($_POST['site_name']);
	
	$site_about  = (isset($_POST['site_about']))? TextBase::HTMLDestruct($_POST['site_about']) : '';
	$keywords    = (isset($_POST['site_keyword']))? TextBase::HTMLDestruct($_POST['site_keyword']) : '';	
	
	if ( TextBase::StringLen($keywords) > 200 ) {
	$info .= 'Ключевые слова занимают больше 200 символов ('.TextBase::StringLen($keywords).').';
	break;
	}
	if ( !TextBase::StringLen($site_name)){	
	$info .= 'Укажите название сайта.';
	break;
	}
	
	$sbuffer     = (!empty($_POST['sbuffer']))? true : false;	
	$rewrite     = (!empty($_POST['rewrite']))? true : false;
	$log  		 = (!empty($_POST['log']))? true : false;
	$comm_revers = (!empty($_POST['comm_revers']))? true : false;
	
	$config['s_name']      = $site_name   ;
	$config['s_about']     = $site_about  ; 	
	$config['s_keywords']  = $keywords    ;	
	$config['sbuffer']     = $sbuffer     ;	
	$config['rewrite']     = $rewrite 	  ;
 	$config['log']  	   = $log	 	  ;
	$config['comm_revers'] = $comm_revers ;
	
	if (SaveOptions()) $info .= 'Файл настроек обновлен.';
	}
	include MCR_STYLE.'admin/constants.html'; 
    break;	
    case 'profile':  
	if ($ban_user) {
        $group_list = GroupMenager::GetList($ban_user->group());
		
		include MCR_STYLE.'admin/profile_main.html'; 
      	
		$skin_def = $ban_user->defaultSkinTrigger();
		$cloak_exist = file_exists($ban_user->getCloakFName()); 

        if ($cloak_exist or !$skin_def) { $rnd = rand(1000,9999); include MCR_STYLE.'admin/profile_skin.html'; }
        if (!$skin_def )                 include MCR_STYLE.'admin/profile_del_skin.html'; 
        if ($cloak_exist )               include MCR_STYLE.'admin/profile_del_cloak.html'; 
		if ($bd_names['iconomy'] )       include MCR_STYLE.'admin/profile_money.html'; 
		
        include MCR_STYLE.'admin/profile_footer.html'; 
    }
    break;
    case 'delete_banip': 
	if (!empty($_GET['ip']) and preg_match("/[0-9.]+$/", $_GET['ip'])) {
	
	$ip = $_GET['ip']; BD("DELETE FROM {$bd_names['ip_banning']} WHERE IP='".TextBase::SQLSafe($ip)."'");
		                  
    $info .= 'IP '.$ip.' разблокирован для регистрации. ';
	} 
    break;
  }

$html .= ob_get_clean(); 
}

if ($do == 'sign') {

	$data = file_get_contents(MCR_STYLE.'img/edit.png');
	if (!$data) exit;
	$data = explode("\x49\x45\x4E\x44\xAE\x42\x60\x82", $data );
	if (sizeof($data) != 2) exit;

	$data[1] = str_replace("\x20", ' ', $data[1]);
	$data[1] = str_replace(array("\r\n", "\n", "\r"),'<br />', $data[1]);
	$data[1] = '<pre style="word-wrap: break-word; white-space: pre-wrap; font-size: 6px; min-width: 640px;">'.$data[1].'</pre>';

	echo $data[1];
	exit;
}

ob_start(); 

echo $server_info;

if ($info) include MCR_STYLE.'admin/info.html';

include MCR_STYLE.'admin/admin.html'; 

$content_main .= ob_get_clean();
?>