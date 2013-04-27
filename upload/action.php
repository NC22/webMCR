<?php
/* WEB-APP : WebMCR (С) 2013 NC22 */

if (empty($_POST['method']) and empty($_GET['method'])) exit;
$method = (isset($_POST['method']))? $_POST['method'] : $_GET['method'];

switch ($method) {
	case 'comment': 
    case 'del_com':
	case 'profile': 
	case 'restore': 
	case 'load_info': 
	case 'upload':
	case 'like':	
	case 'delete_file':	
	
	require('./system.php');
	require(MCR_ROOT.'instruments/ajax.php');	
	require(MCR_ROOT.'instruments/user.class.php');
	
		if ($method == 'upload' or $method == 'delete_file') require(MCR_ROOT.'instruments/upload.class.php');
	elseif ($method == 'profile') require(MCR_ROOT.'instruments/skin.class.php');
	elseif ($method == 'restore' and $config['p_logic'] != 'usual' and $config['p_logic'] != 'xauth') 
		aExit(1,'Восстановление пароля невозможно. Используются скрипты авторизации сторонней CMS.');
	
	BDConnect('action_'.$method);
	MCRAuth::userLoad();
	
    break;
	case 'download':
	
	require('./system.php');
	require(MCR_ROOT.'instruments/upload.class.php');
	
	BDConnect('action_download');
	
	break;
	default: exit; break;
}

function CaptchaTest($exit_mess = 2) { 

	if ( empty($_SESSION['code']) or 
         empty($_POST['antibot']) or 
         $_SESSION['code'] != (int)$_POST['antibot'] ) {
       
            if (isset($_SESSION['code'])) unset($_SESSION['code']);
            aExit($exit_mess, 'Защитный код введен не верно.');

    }
	unset($_SESSION['code']);
}

switch ($method) {
	case 'upload': // TODO Список последних добавленых файлов
		
		if (empty($user) or $user->lvl() < 15) break; // добавить разрешение
		
		$file 	= new File();
		$id_rewrite = (isset($_POST['nf_delete']))? true : false;
		$id_word 	= (!empty($_POST['nf_id_word']))? $_POST['nf_id_word'] : false;
		
		$result = $file->Create('new_file', $user->id(), $id_word, $id_rewrite);
		$error  = '';
		
		switch($result) {
			case 1: $error = 'Ошибка при загрузке файла. ( Допустимые форматы файла - jpg, png, zip, rar, exe, jar, pdf, doc, txt )'; break;
			case 3: $error = 'Текстовый идентификатор содержит недопустимые символы.'; break;
			case 4: $error = 'Файл с таким идентификатором уже существует.'; break;
			case 2: 
			case 5: $error = 'Ошибка добавления файла. Включите лог для просмотра подробной информации.'; break;
			case 6: $error = 'Ошибка при удалении файла с одинаковым идентификатором.'; break;
			case 7: $error = 'Найден идентичный файл.'; break;
		}

		if ($result > 0 and $result != 7) aExit($result, $error);
		
		$file_info = $file->getInfo();
		
		$ajax_message['file_id'] = $file_info['id'];	
		$ajax_message['file_name'] = $file_info['id'];
		$ajax_message['file_size'] = $file_info['size'];
		
		$ajax_message['file_html'] = $file->Show();
		
		aExit($result, $error);
	break;
	case 'like':
	
		if (empty($_POST['type']) or empty($_POST['id']) or !isset($_POST['dislike'])) break;
		if (empty($user)) { 
		
		aExit(3, 'Like not authed'); 
		break;
		}
		
		$id			= (int)$_POST['id'];
		$type		= (int)$_POST['type'];
		$dislike	= ((int)$_POST['dislike'])? true : false;		
		
		if ($type == ItemType::News) {
		
			require_once(MCR_ROOT.'instruments/catalog.class.php');
			
			$item = new News_Item($id);
			
			aExit((int)$item->Like($dislike), 'Like');
		}		
	break;
	case 'download': 
		
		if (empty($_GET['file'])) break;
		
		$file = new File($_GET['file']);
		if (!$file->Download())	header("Location: ".BASE_URL."index.php?mode=404");
	break;
	case 'delete_file':
	
		if (empty($_POST['file'])) break;
		if (empty($user) or $user->lvl() < 15) break;
		
		$file = new File((int)$_POST['file']);
		if ($file->Delete()) aExit(0); else aExit(1);		
	break;
    case 'restore':   
    
        if (empty($_POST['email'])) aExit(1,'Не все поля заполнены.'); 
    
        CaptchaTest(2); 

        $email = $_POST['email'];  
	    
		$result = BD("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['email']}`='".TextBase::SQLSafe($email)."'"); 
		if ( !mysql_num_rows($result) ) aExit(3, 'Пользователь с таким почтовым адрессом не найден.');
		
		$line = mysql_fetch_array( $result, MYSQL_NUM );
        
		$restore_user = new User($line[0],$bd_users['id']);		
	     
		$new_pass = randString(8);
	   
	    $subject = 'Восстановление пароля';
		$message = '<html><body><p>Система восстановления пароля. Ваш логин: '.$restore_user->name().'. Ваш новый пароль : '.$new_pass.'</p></body></html>';
		
		if ( !EMail::Send($email, $subject, $message) ) aExit(4, 'Ошибка службы отправки сообщений.');
		
		if ( $restore_user->changePassword($new_pass) != 1 ) aExit(5, '');
		
		aExit(0, 'Новый пароль отправлен вам на Email.');	

    break;
	case 'comment': 
	
        if (empty($user) or empty($_POST['comment']) or empty($_POST['item_id']) or empty($_POST['antibot'])) aExit(1, 'Ошибка отправки сообщения.'); 

	    if ( !$user->canPostComment() ) aExit(1, 'Отправлять сообщения можно не чаще чем раз в минуту.'); 

	    CaptchaTest(3); 
			
	    require_once(MCR_ROOT.'instruments/catalog.class.php');
				
		$comments_item = new Comments_Item();				
		$rcode = $comments_item->Create($_POST['comment'],(int)$_POST['item_id']);
        
            if ( $rcode == 1701 ) aExit(1, 'Сообщение слишком короткое.');       
        elseif ( $rcode == 1702 ) aExit(2, 'Комментируемая статья или новость не найдена.');       
        elseif ( $rcode == 1 )    aExit(0, 'Сообщение успешно отправлено ');          
        else                      aExit(3, 'Ошибка отправки сообщения.');  

    break;
    case 'del_com':

		if (empty($user) or empty($_POST['item_id'])) aExit(1);		
		
		require_once(MCR_ROOT.'instruments/catalog.class.php');
			
		$comments_item = new Comments_Item((int)$_POST['item_id']);
		
		if (!$user->getPermission('adm_comm') and $comments_item->GetAuthorID() != $user->id()) aExit(1); 
		
		if ($comments_item->Delete()) aExit(0);	else aExit(1);  

    break;
    case 'load_info':

        $ajax_message = array('code' => 0, 
		                      'message' => 'load_info',
 							  'name' => '',
							  'group' => '',
							  'skin' => 0, 
							  'cloak' => 0,
							  'comments_num' => 0,
							  'female' => 0,
							  'play_times' => 0,
							  'undress_times' => 0,
							  'create_time' => 0,
							  'active_last' => 0,
							  'play_last' => 0);

        if (empty($_POST['id'])) aExit(1, 'Поисковой индекс не задан.'); 
         
        $inf_user = new User((int) $_POST['id'],$bd_users['id']);
        if (!$inf_user->id()) aExit(2, 'Пользователь не найден.'); 
        
        $ajax_message['name']   = $inf_user->name();
        $ajax_message['group']  = $inf_user->getGroupName();
		$ajax_message['skin']   = ($inf_user->defaultSkinTrigger())? 1 : 0;
		$ajax_message['female'] = ($inf_user->isFemale())? 1 : 0;
		
		    $timeParam = $inf_user->gameLoginLast();
		if ($timeParam) $ajax_message['play_last'] = strtotime($timeParam);		
		    $timeParam = $inf_user->getStatisticTime('create_time');
		if ($timeParam) $ajax_message['create_time'] = ($config['p_logic'] == 'xenforo' or $config['p_logic'] == 'ipb' or $config['p_logic'] == 'dle')? $timeParam : strtotime($timeParam);			
	        $timeParam = $inf_user->getStatisticTime('active_last');
		if ($timeParam) $ajax_message['active_last'] = strtotime($timeParam);
		
		$statistic = $inf_user->getStatistic();		
		
		if ($statistic) {
		
		$ajax_message['comments_num']  = $statistic['comments_num'];
		$ajax_message['play_times']    = $statistic['play_times'];
		$ajax_message['undress_times'] = $statistic['undress_times'];
		}
		
        aExit(0);	

    break;
	case 'profile': 

        $ajax_message = array('code' => 0, 'message' => 'profile', 'name' => '', 'group' => '', 'id' => '', 'skin' => 0, 'cloak' => 0);

        $rcodes = null;        

        if (empty($user) or $user->lvl() <= 0) aExit(1); 

        $mod_user = $user;
		
        if ($user->lvl() >= 15 and !empty($_POST['user_id'])) 
        $mod_user = new User((int) $_POST['user_id'], $bd_users['id']);

        if (!$mod_user->id()) aExit(2, 'Пользователь не найден.'); 
		
	    if ($user->lvl() >= 15){
		
			if (isset($_POST['new_group'])) {
			
				if ($mod_user->changeGroup((int) $_POST['new_group'])) $rcodes[] = 1;
			}			
			if (!empty($_POST['new_money'])) {
				
				if ($mod_user->addMoney($_POST['new_money'])) $rcodes[] = 1;
			}			
			if (isset($_POST['new_gender'])) {
			
		        $newgender = (!(int)$_POST['new_gender'])? 0 : 1;
                if ($mod_user->changeGender($newgender)) $rcodes[] = 1;
		    }			   
			if (!empty($_POST['new_email'])) 
			
				$rcodes[] = $mod_user->changeEmail($_POST['new_email']);
		}
		
 	    if (!empty($_POST['new_login'])) $rcodes[] = $mod_user->changeName($_POST['new_login']);
	    if (!empty($_POST['new_password'])) {

			$oldpass   = (!empty($_POST['old_password']))? $_POST['old_password'] : '';
			$newpass   =  $_POST['new_password'];
            $newrepass = (!empty($_POST['new_repassword']))? $_POST['new_repassword'] : '';

            if ($user->lvl() >= 15 and !empty($_POST['user_id'])) $rcodes[] = $mod_user->changePassword($newpass);
            else                  	$rcodes[] = $mod_user->changePassword($newpass, $newrepass, $oldpass);
        }
		
        if (empty($_FILES['new_skin']['tmp_name'])  and !empty($_POST['new_delete_skin']) and !$mod_user->defaultSkinTrigger() and $user->getPermission('change_skin')) 
           $rcodes[] = $mod_user->setDefaultSkin();

	    if (empty($_FILES['new_cloak']['tmp_name']) and !empty($_POST['new_delete_cloak']) and $user->getPermission('change_cloak')) { 
			$mod_user->deleteCloak();
			$rcodes[] = 1;
		}
	    if (!empty($_FILES['new_skin']['tmp_name']) ) $rcodes[] = (int) $mod_user->changeVisual('new_skin', 'skin');

	    if (!empty($_FILES['new_cloak']['tmp_name']) ) $rcodes[] = (int) $mod_user->changeVisual('new_cloak', 'cloak').'1'; 
        
        $message = ''; 
        $rnum    = sizeof($rcodes);

        for ($i=0; $i < $rnum; $i++) {

            $modifed = true; 

			switch ((int) $rcodes[$i]) {
                case 0 : $message .= 'error'; break;
                case 1401 : $message .= 'Логин введен некорректно.'.$rnum  ; break;
				case 1402 : $message .= 'Пользователь с таким именем уже существует.'; break;
			    case 1403 : $message .= 'Логин должен содержать не меньше 4 символов и не больше 8.'; break;   
				case 1501 : $message .= 'Пароль введен некорректно.'; break;
                case 1502 : $message .= 'Текущий пароль неверен.'; break;
                case 1503 : $message .= 'Пароль должен содержать не меньше 4 символов и не больше 15.'; break;
                case 1504 : $message .= 'Пароли не совпадают.'; break;
                case 1601 : 
                $message .= "Файл больше ".$user->getPermission('max_fsize')." кб ( загрузка скина )"; 				  
				break;                
				case 16011 : 
				$message .= "Файл больше ".$user->getPermission('max_fsize')." кб ( загрузка плаща )"; 
				break;
                case 1602 : 
				$tmpm = $user->getPermission('max_ratio');
				$message .= "Размеры изображения заданы неверно. ( Рекомендуемое соотношение сторон для скина ".(62*$tmpm)."x".(32*$tmpm)." )"; 
				unset($tmpm);
				break;
                case 16021 : 
				$tmpm = $user->getPermission('max_ratio');
				$message .= "Размеры изображения заданы неверно. ( Рекомендуемое соотношение сторон для плаща ".(22*$tmpm)."x".(17*$tmpm)." )";
                unset($tmpm);
				break;
                case 1604 : $message .= 'Ошибка при загрузке скина. ( Рекомендуемый формат файла .png )'; break;
                case 16041 : $message .= 'Ошибка при загрузке плаща. ( Рекомендуемый формат файла .png )'; break;
                case 1605 : $message .= 'Доступ к загрузке скинов ограничен.'; break;                
				case 16051 : $message .= 'Доступ к загрузке плащей ограничен.'; break;
                case 1610 : $message .= 'Системная папка заблокирована для работы с файлами. Включите лог, чтобы диагностировать проблему.'; 
				case 16101 :
                case 1611 : 
				case 16111 : break;				
				case 1901 : $message .= 'Emai\'l введен некорректно.'; break;
				case 1902 : $message .= 'Почтовый ящик уже используется другим пользователем.'; break;
                default : $modifed = false; break; 
            }	

            if ($modifed) $message .= "\n";	
		}
    
        $ajax_message['name']  = $mod_user->name();
        $ajax_message['group'] = $mod_user->getGroupName();   
        $ajax_message['id']    = $mod_user->id();

        if (file_exists($mod_user->getCloakFName())) $ajax_message['cloak'] = 1; 
        if ($mod_user->defaultSkinTrigger())         $ajax_message['skin']  = 1; 

        	if ($message) aExit(2, $message  ); // some bad news 
		elseif (!$rnum)  aExit(100, $message ); //nothing changed
        else aExit(0, 'Профиль успешно обновлен.');  

    break;
} 
?>