<?php
/* WEB-APP : WebMCR (С) 2013-2014 NC22 | License : GPLv3 */
require('./system.php');

$method = Filter::input('method', 'post', 'string', true);
if ($method === false) $method = Filter::input('method', 'get', 'string', true);

if (!$method) exit;

switch ($method) {
    case 'comment':
    case 'del_com':
    case 'profile':
    case 'restore':
    case 'load_info':
    case 'upload':
    case 'like':
    case 'delete_file':
        
        loadTool('ajax.php');
        loadTool('user.class.php');

        if ($method == 'upload' or $method == 'delete_file')
            loadTool('upload.class.php');
        elseif ($method == 'profile')
            loadTool('skin.class.php');
        elseif ($method == 'restore' and
                $config['p_logic'] != 'usual' and
                $config['p_logic'] != 'xauth' and
                $config['p_logic'] != 'authme')
            aExit(1, 'Change password is not available');

        DBinit('action_' . $method);
        MCRAuth::userLoad();

        break;
    case 'download':
        loadTool('upload.class.php');
        DBinit('action_download');

        break;
    default: exit;
        break;
}

switch ($method) 
{
    case 'upload': // TODO Список последних добавленых файлов

        if (empty($user) or $user->lvl() < 15) break; 

        $file 	= new File(false, 'other/');
        $id_rewrite = Filter::input('nf_delete', 'post', 'bool'); 
        $id_word  = Filter::input('nf_id_word', 'post', 'string', true);

        $result = $file->Create('new_file', $user->id(), $id_word, $id_rewrite);
        $error  = '';

        switch($result) {
                case 1: $error = lng('UPLOAD_FAIL').'. ( '.lng('UPLOAD_FORMATS').' - jpg, png, zip, rar, exe, jar, pdf, doc, txt )'; break;
                case 3: $error = lng('INCORRECT').'. ('.lng('TXT_ID').')'; break;
                case 4: $error = lng('TXT_ID_EXIST'); break;
                case 2: 
                case 5: $error = lng('UPLOAD_FAIL'); break;
                case 6: $error = lng('DEL_FAIL'); break;
                case 7: $error = lng('FILE_EXIST'); break;
        }

        if ($result > 0 and $result != 7) aExit($result, $error);

        $file_info = $file->getInfo();

        $ajax_message['file_id'] = $file_info['id'];	
        $ajax_message['file_name'] = $file_info['name'];
        $ajax_message['file_size'] = $file_info['size'];
        $ajax_message['file_html'] = $file->Show();

        aExit($result, $error);
    break;
    case 'like':
        $id = Filter::input('id', 'post', 'int');
        $type = Filter::input('type', 'post', 'int');
        $dislike = Filter::input('dislike', 'post', 'bool');

        if (!$type or !$id)
            break;
        if (empty($user)) {

            aExit(3, 'Like not authed');
            break;
        }

        $item = null;
        if ($type == ItemType::News) {
            loadTool('catalog.class.php');
            $item = new News_Item($id);
            
        } elseif ($type == ItemType::Skin and isset($bd_names['sp_skins'])) {
            loadTool('skinposer.class.php');
            $item = new SPItem($id);
        }

        if ($item) aExit((int) $item->Like($dislike), 'Like');
    break;
    case 'download': 
        $file = Filter::input('file', 'get');
        if (empty($file)) break;

        $file = new File($file);
        if (!$file->Download()) header("Location: ".BASE_URL."index.php?mode=404");
    break;
    case 'delete_file':
        $file = Filter::input('file', 'post', 'stringLow');
        if (!$file) break;
        if (empty($user) or $user->lvl() < 15) break;

        $file = new File($file);
        if ($file->Delete()) aExit(0); else aExit(1);		
    break;
    case 'restore':
        
        $email = Filter::input('email', 'post', 'mail', true);  
        if (!$email) aExit(1, lng('INCOMPLETE_FORM')); 

        CaptchaCheck(2); 

        $sql = "SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` "
             . "WHERE `{$bd_users['email']}`=:email";

        $result = getDB()->fetchRow($sql, array('email' => $email), 'num');

        if ( !$result ) aExit(3, lng('RESTORE_NOT_EXIST'));

        $restore_user = new User((int)$result[0]);		

        $new_pass = randString(8);

        $subject = lng('RESTORE_TITLE');
        $message = '<html><body><p>'.lng('RESTORE_TITLE').'. '.lng('RESTORE_NEW').' '.lng('LOGIN').': '.$restore_user->name().'. '.lng('PASS').': '.$new_pass.'</p></body></html>';

        if ( !EMail::Send($email, $subject, $message) ) aExit(4, lng('MAIL_FAIL'));

        if ( $restore_user->changePassword($new_pass) != 1 ) aExit(5, '');

        aExit(0, lng('RESTORE_COMPLETE'));	

    break;
    case 'comment': 
        
        $comment = Filter::input('comment');
        $item_type = Filter::input('item_type', 'post', 'int');
        $item_id   = Filter::input('item_id', 'post', 'int');

        CaptchaCheck(3);

        if (empty($user) or !$comment or !$item_type or !$item_id) aExit(1, lng('MESS_FAIL')); 

        loadTool('comment.class.php');

        $comments_item = new Comments_Item(false, 'news/comments/');
        $comments_item->aCreate($comment, $user, $item_id, $item_type );
    break;
    case 'del_com':
        $id = Filter::input('item_id', 'post', 'int');
        if (empty($user) or !$id) aExit(1);		

        loadTool('comment.class.php');

        $comments_item = new Comments_Item($id);

        if (!$user->getPermission('adm_comm') and $comments_item->GetAuthorID() != $user->id()) aExit(1); 

        if ($comments_item->Delete()) aExit(0);	else aExit(1);  

    break;
    case 'load_info':
        
        $id = Filter::input('id', 'post', 'int');
        if (!$id) aExit(1, 'Empty POST param ID'); 

        loadTool('profile.class.php');

        $user_profile = new Profile($id, 'other/');
        $ajax_message['player_info'] = $user_profile->Show();

        aExit(0);

    break;
    case 'profile': 

        $ajax_message = array(
            'code' => 0, 
            'message' => 'profile', 
            'name' => '', 
            'group' => '', 
            'id' => '', 
            'skin' => 0, 
            'cloak' => 0, 
            'skin_link' => '?none',
        );

        $rcodes = null;        

        if (empty($user) or $user->lvl() <= 0) aExit(1); 

        $mod_user = $user;
        $user_id = Filter::input('user_id', 'post', 'int');
        
        if ($user_id and $user->lvl() >= 15) {            
            tokenTool('check');
            
            $mod_user = new User($user_id);
            if (!$mod_user->id()) aExit(2, lng('USER_NOT_EXIST')); 
            
            $group = Filter::input('new_group', 'post', 'int', true);
            $money = Filter::input('new_money', 'post', 'int');       
            $gender = Filter::input('new_gender', 'post', 'int', true);
            $mail = Filter::input('new_email', 'post', 'mail');
                    
            if ($group !== false) {

                if ($mod_user->changeGroup($group))
                    $rcodes[] = 1;
            }
            if ($money) {

                if ($mod_user->addMoney($money))
                    $rcodes[] = 1;
            }
            if ($gender !== false) {

                $gender = (!$gender) ? 0 : 1;
                if ($mod_user->changeGender($gender))
                    $rcodes[] = 1;
            }
            if ($mail)
                $rcodes[] = $mod_user->changeEmail($mail);
            
            $ajax_message['token_data'] = tokenTool('get');
        }
        
        $newlogin = Filter::input('new_login');
        $newpass = Filter::input('new_password');
        $delete_skin = Filter::input('new_delete_skin', 'post', 'bool');
        $delete_cloak = Filter::input('new_delete_cloak', 'post', 'bool');
        
        if ($newlogin and ($user->lvl() >= 15 or $user->getPermission('change_login')))
            $rcodes[] = $mod_user->changeName($newlogin);
        
        if ($newpass) {

            $oldpass = Filter::input('old_password');
            $newrepass = Filter::input('new_repassword');

            if ($user->lvl() >= 15 and $user_id)
                $rcodes[] = $mod_user->changePassword($newpass);
            else
                $rcodes[] = $mod_user->changePassword($newpass, $newrepass, $oldpass);
        }

        if (empty($_FILES['new_skin']['tmp_name']) and
            $delete_skin and 
            !$mod_user->defaultSkinTrigger() and 
            $user->getPermission('change_skin'))
           
            $rcodes[] = $mod_user->setDefaultSkin();

        if (empty($_FILES['new_cloak']['tmp_name']) and 
            $delete_cloak and 
            $user->getPermission('change_cloak')) {
            $mod_user->deleteCloak();
            $rcodes[] = 1;
        }
        
        if (!empty($_FILES['new_skin']['tmp_name']) and ($user->lvl() >= 15 or $user->getPermission('change_skin')))
            $rcodes[] = (int) $mod_user->changeVisual('new_skin', 'skin');

        if (!empty($_FILES['new_cloak']['tmp_name']) and ($user->lvl() >= 15 or $user->getPermission('change_cloak')))
            $rcodes[] = (int) $mod_user->changeVisual('new_cloak', 'cloak') . '1';

        $message = ''; 
        $rnum = sizeof($rcodes);

        for ($i=0; $i < $rnum; $i++) {

            $modifed = true; 

            switch ((int) $rcodes[$i]) 
            {
                case 0 : $message .= 'error'; break;
                case 1401 : $message .= lng('INCORRECT').'. ('.lng('LOGIN').')'; break;
                case 1402 : $message .= lng('AUTH_EXIST_LOGIN'); break;
                case 1403 : $message .= lng('INCORRECT_LEN').'. ('.lng('LOGIN').')'; break;   
                case 1501 : $message .= lng('INCORRECT').'. ('.lng('PASS').')'; break;
                case 1502 : $message .= lng('CURPASS_FAIL'); break;
                case 1503 : $message .= lng('INCORRECT_LEN').'. ('.lng('PASS').')'; break;
                case 1504 : $message .= lng('REPASSVSPASS'); break;
                case 1601 : 
                    $message .= lng('MAX_FILE_SIZE').' '.$user->getPermission('max_fsize').lng('KB').' ( '.lng('SKIN_UPLOAD').' )'; 				  
                    break;                
                case 16011 : 
                    $message .= lng('MAX_FILE_SIZE').' '.$user->getPermission('max_fsize').lng('KB').' ( '.lng('CLOAK_UPLOAD').' )';  
                    break;
                case 1602 : 
                    $tmpm = $user->getPermission('max_ratio');
                    $message .= lng('MAX_FILE_RATIO').' '.(62*$tmpm)."x".(32*$tmpm).' ( '.lng('SKIN_UPLOAD').' )'; 
                    unset($tmpm);
                    break;
                case 16021 : 
                    $tmpm = $user->getPermission('max_ratio');
                    $message .= lng('MAX_FILE_RATIO').' '.(22*$tmpm)."x".(17*$tmpm).' ( '.lng('CLOAK_UPLOAD').' )';
                    unset($tmpm);
                break;
                case 1604 : $message .= lng('UPLOAD_FAIL').' ( '.lng('SKIN_UPLOAD').' ) '.lng('UPLOAD_FORMATS').' - .png'; break;
                case 16041 : $message .= lng('UPLOAD_FAIL').' ( '.lng('CLOAK_UPLOAD').' ) '.lng('UPLOAD_FORMATS').' - .png'; break;
                case 1605 : $message .= lng('PERMISSION_FAIL'); break;                
                case 16051 : $message .= lng('PERMISSION_FAIL'); break;
                case 1610 : $message .= lng('UPLOAD_FAIL'); 
                case 16101 :
                case 1611 : 
                case 16111 : break;				
                case 1901 : $message .= lng('INCORRECT').'. ('.lng('EMAIL').')'; break;
                case 1902 : $message .= lng('AUTH_EXIST_EMAIL'); break;
                default : $modifed = false; break; 
            }	

            if ($modifed) $message .= "\n";	
        }

        $ajax_message['name']  = $mod_user->name();
        $ajax_message['group'] = $mod_user->getGroupName();   
        $ajax_message['id']    = $mod_user->id();

        $ajax_message['skin_link'] = $mod_user->getSkinLink(false, '&', true);
        $ajax_message['mskin_link'] = $mod_user->getSkinLink(true, '&', true);

        if (file_exists($mod_user->getCloakFName())) $ajax_message['cloak'] = 1; 
        if ($mod_user->defaultSkinTrigger())         $ajax_message['skin']  = 1; 

            if ($message) aExit(2, $message  ); // some bad news 
        elseif (!$rnum)  aExit(100, $message ); //nothing changed
        else aExit(0, lng('PROFILE_COMPLITE'));  

    break;
} 
