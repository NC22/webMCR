<?php 
if (!defined('MCR')) exit; 

class CommentList extends View {
private $work_script;

private $parent_obj;

private $per_page;
private $revers;

private $db;

    public function __construct($parent, $work_script, $style_sd = false) { 
    global $bd_names, $config;
    
		$this->parent_obj = false;
		
		if ( !$parent->Exist() ) return false;
			
		$this->parent_obj = $parent;
		
        parent::View($style_sd);
		
        $this->work_script = $work_script;
        
        $this->per_page = $config['comm_by_page'];
        $this->revers   = $config['comm_revers'];
        
        $this->db = $bd_names['comments'];
    }
	
	public function ShowAddForm() {
	global $user;
	
	if ( $this->parent_obj === false ) return '';
	if (empty($user) or !$user->getPermission('add_comm')) return '';
	
	$id = $this->parent_obj->id();
	$type = $this->parent_obj->type();
	
	ob_start();
		
	include $this->GetView('comments_add.html');
				  
	return ob_get_clean();
	}
    
    public function Show($list = false) {
    global $user;
	
		if ( $this->parent_obj === false ) return '';
		
        $sql_where = "`item_id`='". $this->parent_obj->id() ."' AND `item_type`='" . $this->parent_obj->type() . "'";
		
        $result = BD("SELECT COUNT(*) FROM `{$this->db}` WHERE " . $sql_where);
        $line = mysql_fetch_array($result);
        
        $comments_html = '';  
        $arrows_html = '';  
        
        $commentnum = $line[0];
        if ($commentnum) {
            
            $comm_pnum = $this->per_page; 
            $comm_order = ($this->revers)? 'ASC' : 'DESC';	
            
            $list_def = ($this->revers)? ceil($commentnum / $comm_pnum) : 1;	
            $list = ($list <= 0)? $list_def : (int)$list;		
            
            $result = BD("SELECT `id` FROM `{$this->db}` WHERE " . $sql_where . " ORDER by time ". $comm_order ." LIMIT ".($comm_pnum*($list-1)).",".$comm_pnum); 
            if ( mysql_num_rows( $result ) != 0 ) {			
            
            while ( $line = mysql_fetch_array( $result, MYSQL_NUM ) ) {
            
                     $comments_item = new Comments_Item($line[0], $this->st_subdir);
                     
                     $comments_html .= $comments_item->Show($user); 
                     unset($comments_item);
            }
            
            $arrows_html = $this->arrowsGenerator($this->work_script, $list, $commentnum, $comm_pnum);		  
            }
		}		
				
		ob_start(); include $this->GetView('comments_container.html');					  
		return ob_get_clean();		
    }
	
}

class Comments_Item extends Item { 
private $user_id;
private $parent_obj;

    public function __construct($id = false, $style_sd = false) {
    global $bd_names;	

    parent::__construct($id, ItemType::Comment, $bd_names['comments'], $style_sd);

	if (!$this->id) return false;	
	
    $result = BD("SELECT `user_id`, `item_id`, `item_type` FROM `{$this->db}` WHERE `id`='". $this->id ."'"); 
 
        if ( mysql_num_rows( $result ) != 1 ) { $this->id = false; return false; }
  
    $line = mysql_fetch_array($result, MYSQL_NUM);   
  
    $this->user_id = (int) $line[0];
	$this->parent_id = (int) $line[1];	
	$this->parent_type = (int) $line[2];
    $this->parent_obj = false;		 	
    }
	
	private function initParent() {
	global $bd_names;

		if ($this->parent_obj and $this->parent_obj->Exist()) return true; 
	
			if ( $this->parent_type === ItemType::News ) { 
			
			loadTool('catalog.class.php'); 
			$this->parent_obj = new News_Item($this->parent_id);
 			
		} elseif ( $this->parent_type === ItemType::Skin and isset($bd_names['sp_skins'])) { 
		
			loadTool('skinposer.class.php'); 
			$this->parent_obj = new SPItem($this->parent_id); 
			
		} else return false;
		
		if (!$this->parent_obj->Exist()) { $this->parent_obj = false; return false; }
		
		// ToDo custom init code from eval or include
		
		return true;
	}
	
	public function aCreate($message, $fUser, $item_id, $item_type, $antibot = 'antibot') {
	global $ajax_message, $config;
	
		if ($this->id) return 0;

		$this->parent_id = (int) $item_id;	
		$this->parent_type = (int) $item_type;
		
		if ( !$this->initParent() ) aExit(2, lng('MESS_NOT_FOUND')); 
		
		loadTool('ajax.php');
				
		if ( !$fUser->id() ) aExit(1, lng('MESS_FAIL'));
		if ( !$fUser->canPostComment() ) aExit(1, lng('MESS_TIMEOUT')); 

		if ($antibot) CaptchaCheck(3, true, $antibot);			
									
			$rcode = $this->Create($message, $fUser->id(), $item_id, $item_type );
			
			if ( $rcode == 1701 ) aExit(1, lng('MESS_SHORT'));       
		elseif ( $rcode == 1702 or $rcode == 1703 ) aExit(2, lng('MESS_NOT_FOUND'));       
		elseif ( $rcode == 1 ) {            

                                $ajax_message['comment_html'] = $this->Show($fUser); 
                                $ajax_message['comment_revers'] = $config['comm_revers'];
                                
								$fUser->setStatistic('comments_num', 1);
		    
								aExit(0, lng('MESS_COMPLITE')); 
									
		} else aExit(3, lng('MESS_FAIL'));	
	}
	
	public function Create($message, $user_id, $item_id, $item_type) {
	
		if ($this->id) return 0;

		$this->parent_id = (int) $item_id;	
		$this->parent_type = (int) $item_type;		
		
		if ( !$this->initParent() ) return 1703;
		
        $this->user_id = $user_id;

		$message = Message::Comment($message);
		if ( TextBase::StringLen($message) < 2 ) return 1701;		
			
		// lock read \ write cause comments may asked to be shown \ delete while creation
		
		if ( BD("INSERT INTO `{$this->db}` ( `message`, `time` , `item_id`, `item_type`, `user_id`) values ('".TextBase::SQLSafe($message)."', NOW(), '". $this->parent_obj->id() ."', '". $this->parent_obj->type() ."', '".$this->user_id."')") ) {
		
		$this->id = mysql_insert_id();
		$this->parent_obj->OnComment();			
		
		return 1; 	
		}
		
	return 0;
	}
    
    public function Show($for_user = false) {

        if (!$this->Exist()) return $this->ShowPage('comment_not_found.html');
        
        $result = BD("SELECT DATE_FORMAT(time, '%d.%m.%Y | %H:%i:%S') AS time, message, item_id FROM `{$this->db}` WHERE `id`='" . $this->id . "'" ); 
        if (!mysql_num_rows( $result )) return ''; 
        
        $line = mysql_fetch_array( $result, MYSQL_ASSOC );
        
        $admin_buttons  = '';
        $female_mark    = '';
        $text           = Message::BBDecode($line['message']);
        $date           = $line['time'];
        
        $id      = $this->id;
        $item_id = $line['item_id'];
        
        $this_user = new User($this->user_id);
        
        $user_id     = $this->user_id;
        $user_name   = ($this_user->id())? $this_user->name() : 'Banned';
        $user_female = ($this_user->id())? $this_user->isFemale() : false;	
        
        $user_img_get = $this_user->getSkinLink(true);

        if ( $for_user and ( $for_user->getPermission('adm_comm') or $for_user->id() == $this->user_id ) ) { 

            ob_start(); include $this->GetView('comments_admin.html');			  
            $admin_buttons = ob_get_clean();
        }

        if ( $user_female ) $female_mark = $this->ShowPage('comments_girl.html');
        
        ob_start();	
        if ($for_user) include $this->GetView('comments.html');
        else           include $this->GetView('comments_unauth.html');
    
    return ob_get_clean();
    }

    public function getAuthorID() {
        if (!$this->Exist()) return false;		
        return $this->user_id;
    }

    public function Edit($message) {
        $message = Message::Comment($message);				
        BD("UPDATE `{$this->db}` SET message='".TextBase::SQLSafe($message)."' WHERE `id`='" . $this->id . "'");
        
        return true; 
    }
	
	public function Delete() {
	
	if (!$this->Exist()) return false;

	if ( $this->initParent() ) $this->parent_obj->OnDeleteComment();
	
	return parent::Delete();	
	}
}