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
	elseif ($method == 'restore' and 
			$config['p_logic'] != 'usual' and 
			$config['p_logic'] != 'xauth' and
			$config['p_logic'] != 'authme') 
		aExit(1,'Change password is not available');
	
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

switch ($method) {
	case 'upload': // TODO Список последних добавленых файлов
		
		if (empty($user) or $user->lvl() < 15) break; // добавить разрешение
		
		$file 	= new File();
		$id_rewrite = (isset($_POST['nf_delete']))? true : false;
		$id_word 	= (!empty($_POST['nf_id_word']))? $_POST['nf_id_word'] : false;
		
		$result = $file->Create('new_file', $user->id(), $id_word, $id_rewrite);
		$error  = '';
		
		switch($result) {
			case 1: $error = _('UPLOAD_FAIL').'. ( '._('UPLOAD_FORMATS').' - jpg, png, zip, rar, exe, jar, pdf, doc, txt )'; break;
			case 3: $error = _('INCORRECT').'. ('._('TXT_ID').')'; break;
			case 4: $error = _('TXT_ID_EXIST'); break;
			case 2: 
			case 5: $error = _('UPLOAD_FAIL'); break;
			case 6: $error = _('DEL_FAIL'); break;
			case 7: $error = _('FILE_EXIST'); break;
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
		$item		= null;
		
		if ($type == ItemType::News) {
		
			require_once(MCR_ROOT.'instruments/catalog.class.php');
			
			$item = new News_Item($id);
			
		} elseif ($type == ItemType::Skin and file_exists(MCR_ROOT.'instruments/skinposer.class.php')) {

			require_once(MCR_ROOT.'instruments/skinposer.class.php');
			
			$item = new SPItem($id);
		}
		
		if ($item) aExit((int)$item->Like($dislike), 'Like');	
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
    
        if (empty($_POST['email'])) aExit(1, _('INCOMPLETE_FORM')); 
    
        CaptchaCheck(2); 

        $email = $_POST['email'];  
	    
		$result = BD("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['email']}`='".TextBase::SQLSafe($email)."'"); 
		if ( !mysql_num_rows($result) ) aExit(3, _('RESTORE_NOT_EXIST'));
		
		$line = mysql_fetch_array( $result, MYSQL_NUM );
        
		$restore_user = new User($line[0],$bd_users['id']);		
	     
		$new_pass = randString(8);
	   
	    $subject = _('RESTORE_TITLE');
		$message = '<html><body><p>'._('RESTORE_TITLE').'. '._('RESTORE_NEW').' '._('LOGIN').': '.$restore_user->name().'. '._('PASS').': '.$new_pass.'</p></body></html>';
		
		if ( !EMail::Send($email, $subject, $message) ) aExit(4, _('MAIL_FAIL'));
		
		if ( $restore_user->changePassword($new_pass) != 1 ) aExit(5, '');
		
		aExit(0, _('RESTORE_COMPLETE'));	

    break;
	case 'comment': 
	
        if (empty($user) or empty($_POST['comment']) or empty($_POST['item_id']) or empty($_POST['antibot'])) aExit(1, _('MESS_FAIL')); 

	    if ( !$user->canPostComment() ) aExit(1, _('MESS_TIMEOUT')); 

	    CaptchaCheck(3); 
			
	    require_once(MCR_ROOT.'instruments/catalog.class.php');
				
		$comments_item = new Comments_Item();				
		$rcode = $comments_item->Create($_POST['comment'],(int)$_POST['item_id']);
        
            if ( $rcode == 1701 ) aExit(1, _('MESS_SHORT'));       
        elseif ( $rcode == 1702 ) aExit(2, _('MESS_NOT_FOUND'));       
        elseif ( $rcode == 1 )    aExit(0, _('MESS_COMPLITE'));          
        else                      aExit(3, _('MESS_FAIL'));  

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

        if (empty($_POST['id'])) aExit(1, 'Empty POST param ID'); 
         
        $inf_user = new User((int) $_POST['id'],$bd_users['id']);
        if (!$inf_user->id()) aExit(2, _('USER_NOT_EXIST')); 
        
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

        if (!$mod_user->id()) aExit(2, _('USER_NOT_EXIST')); 
		
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
                case 1401 : $message .= _('INCORRECT').'. ('._('LOGIN').')'; break;
				case 1402 : $message .= _('AUTH_EXIST_LOGIN'); break;
			    case 1403 : $message .= _('INCORRECT_LEN').'. ('._('LOGIN').')'; break;   
				case 1501 : $message .= _('INCORRECT').'. ('._('PASS').')'; break;
                case 1502 : $message .= _('CURPASS_FAIL'); break;
                case 1503 : $message .= _('INCORRECT_LEN').'. ('._('PASS').')'; break;
                case 1504 : $message .= _('REPASSVSPASS'); break;
                case 1601 : 
                $message .= _('MAX_FILE_SIZE').' '.$user->getPermission('max_fsize')._('KB').' ( '._('SKIN_UPLOAD').' )'; 				  
				break;                
				case 16011 : 
				$message .= _('MAX_FILE_SIZE').' '.$user->getPermission('max_fsize')._('KB').' ( '._('CLOAK_UPLOAD').' )';  
				break;
                case 1602 : 
				$tmpm = $user->getPermission('max_ratio');
				$message .= _('MAX_FILE_RATIO').' '.(62*$tmpm)."x".(32*$tmpm).' ( '._('SKIN_UPLOAD').' )'; 
				unset($tmpm);
				break;
                case 16021 : 
				$tmpm = $user->getPermission('max_ratio');
				$message .= _('MAX_FILE_RATIO').' '.(22*$tmpm)."x".(17*$tmpm).' ( '._('CLOAK_UPLOAD').' )';
                unset($tmpm);
				break;
                case 1604 : $message .= _('UPLOAD_FAIL').' ( '._('SKIN_UPLOAD').' ) '._('UPLOAD_FORMATS').' - .png'; break;
                case 16041 : $message .= _('UPLOAD_FAIL').' ( '._('CLOAK_UPLOAD').' ) '._('UPLOAD_FORMATS').' - .png'; break;
                case 1605 : $message .= _('PERMISSION_FAIL'); break;                
				case 16051 : $message .= _('PERMISSION_FAIL'); break;
                case 1610 : $message .= _('UPLOAD_FAIL'); 
				case 16101 :
                case 1611 : 
				case 16111 : break;				
				case 1901 : $message .= _('INCORRECT').'. ('._('EMAIL').')'; break;
				case 1902 : $message .= _('AUTH_EXIST_EMAIL'); break;
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
        else aExit(0, _('PROFILE_COMPLITE'));  

    break;
} 
?>