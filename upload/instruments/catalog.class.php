<?php
if (!defined('MCR')) exit;

/* Классы
 
 - Менеджер каталогизатора новостей
 - Новость
 - Категории новостей 

*/

Class Category {
private $db;

private $id;
private $name;
private $priority;

    public function Category($id = false) {
	global $bd_names;
	
		$this->db = $bd_names['news_categorys']; 
		$this->id = (int)$id; if (!$this->id) return false;

		$result = BD("SELECT `name`,`priority` FROM `".$this->db."` WHERE `id`='".$this->id."'"); 
		if ( mysql_num_rows( $result ) != 1 ) { $this->id = false; return false; }
		
        $line = mysql_fetch_array( $result, MYSQL_NUM );
		
		$this->name 	= $line[0];
		$this->priority = (int)$line[1];
	}

	public function Exist() {
		if (!$this->id) return false;	
		else return true;
	}
	
	public function Create($name, $priority = 1, $description = '') {
		
	if ($this->Exist()) return false; 
		      
		if (!$name or !TextBase::StringLen($name)) return false;
		
		$result = BD("SELECT COUNT(*) FROM `".$this->db."` WHERE `name`='".TextBase::SQLSafe($name)."'");
		$num    = mysql_fetch_array($result, MYSQL_NUM);
		if ($num[0]) return false;				
		
		$priority    = (int) $priority;

		if (BD("INSERT INTO `".$this->db."` ( `name`, `priority`, `description`) values ( '".TextBase::SQLSafe($name)."', '".TextBase::SQLSafe($priority)."','".TextBase::SQLSafe($description)."' )"))		
		
		$this->id = mysql_insert_id();
		
		else return false;
		
	return true; 
	}
	
	public function IsSystem() {
       if ($this->id == 1) return true; else return false;
    }	
	
	public function GetName() {
		if (!$this->Exist()) return false;
		return $this->name;		
    }
	
	public function GetPriority() {
		if (!$this->Exist()) return false;
		return $this->priority;				
	}
	
	public function GetDescription() {
		$result = BD("SELECT `description` FROM `".$this->db."` WHERE id='".$this->id."'"); 

		if ( mysql_num_rows( $result ) != 1 ) return false;
        $line = mysql_fetch_array( $result, MYSQL_NUM );
		  
		return $line[0];		
	}
	
	public function Edit($name, $priority = 1, $description = '') {
		
		if (!$this->Exist()) return false; 
		
		if (!$name or !TextBase::StringLen($name)) return false;
		
		$result = BD("SELECT COUNT(*) FROM `".$this->db."` WHERE `name`='".TextBase::SQLSafe($name)."' and `id`!='".$this->id."'");
		$num  	= mysql_fetch_array($result, MYSQL_NUM);
		if ($num[0]) return false;	
				
		$priority    = (int) $priority; 
		
		BD("UPDATE `".$this->db."` SET `name`='".TextBase::SQLSafe($name)."',`priority`='".TextBase::SQLSafe($priority)."',`description`='".TextBase::SQLSafe($description)."' WHERE `id`='".$this->id."'"); 		
		
		$this->name 	= $name;
		$this->priority = $priority;
		return true; 
	}	
	
	public function Delete() {
	global $bd_names;	

		if (!$this->Exist() or $this->IsSystem()) return false;
		
		$result = BD("SELECT `id` FROM `{$bd_names['news']}` WHERE `category_id`='".$this->id."'"); 
		if ( mysql_num_rows( $result ) != 0 ) {
	  
		  while ( $line = mysql_fetch_array( $result, MYSQL_NUM ) ) {
		  		
				$news_item = new News_Item($line[0]);
				$news_item->Delete(); 
				unset($news_item);
		  }
		}
		
		BD("DELETE FROM `".$this->db."` WHERE `id`='".$this->id."'");
		$this->id = false;
		return true; 
	}	
}

Class CategoryManager {

	public static function GetList($selected = 1) {
	global $bd_names;
	
		$result = BD("SELECT `id`,`name` FROM {$bd_names['news_categorys']} ORDER BY `priority` DESC LIMIT 0,90");  
		$cat_list = '';
							
		while ( $line = mysql_fetch_array( $result, MYSQL_ASSOC ) ) 
		 $cat_list .= '<option value="'.$line['id'].'" '.(($selected == $line['id'])?'selected':'').'>'.$line['name'].'</option>';
	
    return $cat_list;	
	}
	
	public static function GetNameByID($id) {

        if (!$id or $id < 0) return 'Без категории';
		
		$cat_item = new Category($id);
		$category_name = $cat_item->GetName();
		
		unset($cat_item);
		
		if (!$category_name) return 'Без категории';
		                else return $category_name;
	}
	
	public static function ExistByID($id) {
		
		$cat_id   = (int) $id;		
		$cat_item = new Category($id);
		
	return $cat_item->Exist();
	}
}

/* Класс записи в каталоге */

Class News_Item extends Item {
private $category_id;
private $discus;
private $title;
private $vote;

private $link;
private $link_work;

private $comments;

	public function __construct($id = false, $style_sd = false ) {
	global $bd_names;
	
		parent::__construct($id, ItemType::News, $bd_names['news'], $style_sd);
		if (!$this->id ) return false;
		
		$result = BD("SELECT `category_id`, `title`, `discus`, `comments`, `vote` FROM `{$this->db}` WHERE `id`='".$this->id."'"); 
		if ( mysql_num_rows( $result ) != 1 ) { $this->id = false; return false; }
		
		$line = mysql_fetch_array( $result, MYSQL_ASSOC );
		
		$this->category_id	= (int) $line['category_id'];	
		$this->title		= $line['title'];	
		$this->discus		= ((int) $line['discus'] == 1) ? true : false;	
		$this->vote			= ((int) $line['vote'] == 1) ? true : false;
		
		$this->link 	 = Rewrite::GetURL(array('news', $this->id), array('', 'id'));
		$this->link_work = 'index.php?id=' . $this->id . '&amp;';
		
		$this->comments = (int) $line['comments'];
		
	return true;
	}
	
	public function Create($cat_id, $title, $message, $message_full = false, $vote = true, $discus = true) {
	global $user;	
		
		if ($this->Exist() or empty($user) or !$user->getPermission('add_news')) return false; 
					
	    $sql = ''; $sql2 = '';		
	    if ( $message_full ) { $sql = ' `message_full`,'; $sql2 = "'".TextBase::SQLSafe($message_full)."', "; }
		
		$cat_id = (int) $cat_id;
		if (!CategoryManager::ExistByID($cat_id)) return false; 

		$vote  = ($vote) ? 1 : 0;
		$discus	= ($discus) ? 1 : 0;
		
		BD("INSERT INTO `{$this->db}` ( `title`, `message`, ".$sql." `time`, `category_id`, `user_id`, `discus`, `vote`) VALUES ( '".TextBase::SQLSafe($title)."', '".TextBase::SQLSafe($message)."', ".$sql2."NOW(), '".TextBase::SQLSafe($cat_id)."', '".$user->id()."', '".$discus."', '".$vote."' )");
		
		$this->id = mysql_insert_id();
		
		$this->category_id 	= $cat_id;
		$this->title 		= $title;
						
		$this->discus   = ($discus == 1) ? true : false;	
		$this->vote		= ($vote == 1) ? true : false;
		
		$this->comments = 0;
		
	return true; 
	}

	public function Like($dislike = false) {
	global $user;
	
		if (!$this->Exist() or empty($user) or !$user->lvl()) return 0;		
		
        $like = new ItemLike(ItemType::News, $this->id, $user->id());

		return $like->Like($dislike);
	}	
	
	public function OnComment() {
	
		if ( !$this->Exist()) return false;
		
		BD("UPDATE `{$this->db}` SET `comments` = comments + 1 WHERE `id`='". $this->id ."'");	
		$this->comments++;
	}
	
	public function OnDeleteComment() {
	
		if ( !$this->Exist()) return false;
		
		BD("UPDATE `{$this->db}` SET `comments` = comments - 1 WHERE `id`='". $this->id ."'");	
		$this->comments--;
	}
	
	public function categoryID() {
		if (!$this->Exist()) return false;		
        return $this->category_id;		
	}
	
	public function title() {
		if (!$this->Exist()) return false;		
        return $this->title;		
	}
	
	public function getInfo() { 
		if (!$this->Exist()) return false; 
		
		$result = BD("SELECT `message`, `message_full` FROM `{$this->db}` WHERE `id`='".$this->id."'"); 
		if (!mysql_num_rows( $result )) return ''; 
		
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		
		return array (	'id' 		=> $this->id,
						'type' 		=> $this->type(),
						'title' 	=> $this->title,
						'vote'		=> $this->vote,
						'discus'	=> $this->discus,
						'comments'	=> $this->comments,
						'text'		=> $line['message'],
						'text_full'	=> $line['message_full'], 
						'category_id' => $this->category_id, );		
	}
	
	public function Show($full_text = false) {
    global $config, $user, $bd_names;
	
	if (!$this->Exist()) return $this->ShowPage('news_not_found.html');
		
		$sql_text = ( $full_text )? ' `message_full`,' : ''; 
				
		$sql_hits = ' `hits`,';
		
		if ( $full_text ) {
		
			BD("UPDATE `{$this->db}` SET `hits` = LAST_INSERT_ID( `hits` + 1 ) WHERE `id`='".$this->id."'");
			$sql_hits = " LAST_INSERT_ID() AS hits,"; 
		}
		
		$sql_likes = ( $this->vote ) ? ' `likes`, `dislikes`,' : '';
		
		$result = BD("SELECT DATE_FORMAT(time,'%d.%m.%Y') AS `date`, DATE_FORMAT(time,'%H:%i') AS `time`, ".$sql_hits.$sql_likes.$sql_text." `message` FROM `{$this->db}` WHERE `id`='".$this->id."'"); 
		if (!mysql_num_rows( $result )) return ''; 
		
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		
		if ($full_text) $line['message_full'] = TextBase::CutWordWrap($line['message_full']);
		$line['message'] = TextBase::CutWordWrap($line['message']);
		
		$text = ( $full_text and TextBase::StringLen($line['message_full']) )? $line['message_full'] : $line['message'];		
		
		$id    = $this->id;
		$title = $this->title;
		$date  = $line['date'];
		$time  = $line['time'];
		
		$vote = ( $this->vote ) ? true : false;

		$likes = ( $this->vote ) ? $line['likes'] : '-';
		$dlikes = ( $this->vote ) ? $line['dislikes'] : '-';
		
		$hits  = $line['hits'];		 

		$link  = $this->link;
		
		$comments = $this->comments;
		
		$category_id = $this->category_id;		
        $category    = CategoryManager::GetNameByID($category_id);
		
		$category_link = Rewrite::GetURL(array('category', $category_id), array('', 'cid'));
		
		$admin_buttons = '';
		
		if (!empty($user) and $user->getPermission('add_news')) { 

		ob_start();	include $this->GetView('news_admin.html');		  
		$admin_buttons =  ob_get_clean();
		} 

		ob_start();	

		if ( $full_text )
		
		 include $this->GetView('news_full.html');
		 
		else
		
		 include $this->GetView('news.html');	
		 
		return ob_get_clean();				
	}
	
	public function ShowFull($comment_list = false) { 
	global $config, $bd_names;

	$link  = Rewrite::GetURL(array('news', $this->id), array('', 'id'));

		$item_exist = $this->Exist();
		
		$title      	= ($item_exist)? $this->title() : 'Новость не найдена'; 			
		$category_id 	= ($item_exist)? $this->categoryID() : 0;
		$category 		= ($item_exist)? CategoryManager::GetNameByID($category_id) : 'Без категории';
		$category_link 	= Rewrite::GetURL(array('category', $category_id), array('', 'cid'));
		
	    ob_start(); include $this->GetView('news_full_header.html');					  
	    $html = ob_get_clean();

		$html .= $this->Show(true);		
		
		if (!$item_exist) return $html;
		
		loadTool('comment.class.php');

		$comments = new CommentList($this, $this->link_work, $this->st_subdir . 'comments/');
		$html .= $comments->Show($comment_list);
	
		if ($this->discus) $html .= $comments->ShowAddForm();
	
	return $html;	
	}
	
	public function Edit($cat_id, $title, $message, $message_full = false, $vote = true, $discus = true ) {
	global $user;
		
		if (!$this->Exist() or empty($user) or !$user->getPermission('add_news')) return false; 
		
		$cat_id = (int)$cat_id;
		if (!CategoryManager::ExistByID($cat_id)) return false; 
				
		if (!$message_full) $message_full = '';
		
		$vote   = ($vote)   ? 1 : 0;
		$discus	= ($discus) ? 1 : 0;
		
		BD("UPDATE `{$this->db}` SET `message`='".TextBase::SQLSafe($message)."',`title`='".TextBase::SQLSafe($title)."',`message_full`='".TextBase::SQLSafe($message_full)."',`category_id`='".TextBase::SQLSafe($cat_id)."', `discus`='".$discus."', `vote`='".$vote."' WHERE `id`='".$this->id."'"); 		
		
		$this->category_id 	= (int)$cat_id;
		$this->title 		= $title;
				
		$this->discus   = ($discus == 1) ? true : false;	
		$this->vote		= ($vote == 1) ? true : false;
		
		return true; 
	}	
		
	public function Delete() {
	global $user, $bd_names;
	
		if (empty($user) or 
		    !$user->getPermission('add_news') or 
			!$this->Exist()) return false; 
		
		$result = BD("SELECT id FROM `{$bd_names['comments']}` WHERE `item_id`='".$this->id."' AND `item_type` = '". $this->type() ."'"); 
		if ( mysql_num_rows( $result ) != 0 ) {
	  
		loadTool('comment.class.php');
	  
		  while ( $line = mysql_fetch_array( $result, MYSQL_NUM ) ) {
		  		
				$comments_item = new Comments_Item($line[0], false);
				$comments_item->Delete(); 
				unset($comments_item);
		  }
		}
	
		BD("DELETE FROM `{$bd_names['likes']}` WHERE `item_id` = '".$this->id."' AND `item_type` = '". $this->type() ."'");
		
		return parent::Delete(); 
	}	
}

/* Менеджер вывода записей из каталога */

Class NewsManager extends View {
private $work_link;
private $category_id;

    public function NewsManager($category = 1, $style_sd = false, $work_link = 'index.php?') { // category = -1 -- all last news
	
		parent::View($style_sd);
	
		if ((int) $category <= 0) $category = 0;
		
		$this->category_id = (int)$category; 
		$this->work_link = $work_link;
	}
	
	public function destroy() {

	  unset($this->work_link); 
	  unset($this->category_id);   
	}

    public function ShowCategorySelect() {
	$cat_list = '<option value="0">Последние новости</option>';	
	$cat_list .= CategoryManager::GetList($this->category_id); 

	ob_start();
	include $this->GetView('categorys.html');

	return ob_get_clean();	
	}	
	
	public function ShowNewsEditor() { 
	global $bd_names;

	$editorTitle  = 'Добавить новость';
	$editorButton = 'Добавить';
	
	$editInfo = array ( 'vote' => isset($_POST['hide_vote']) ? false : true, 'discus' => isset($_POST['hide_discus']) ? false : true );
	$editCategory = 0;
	$editMode     = 0;
	$editTitle    = InputGet('title');
	$editMessage  = InputGet('message');
	$editMessage_Full  = InputGet('message_full');
	$error        = '';

		if (isset($_POST['title']) and isset($_POST['message']) and isset($_POST['cid'])) {

             ob_start();
			 $state = 'error';
			
			if (empty($_POST['title']) or empty($_POST['message']) or empty($_POST['cid'])) 
			
			    $text_str = 'Заполните необходимые поля.';
			
			else {
			
			    $mesFull = (!empty($_POST['message_full']))? $_POST['message_full'] : false;
			
				$title = $_POST['title'];
				$mes   = $_POST['message'];
				
				$editMode 		= (int) $_POST['editMode'];
				$editCategory 	= (int) $_POST['cid'];
				
				if ($editMode > 0) {
				
				    $news_item = new News_Item($editMode, $this->st_subdir);
				
				if ($news_item->Edit($editCategory, $title, $mes, $mesFull, $editInfo['vote'], $editInfo['discus'])) {
				
				    $state       = 'success';
				    $text_str    = 'Новость обновлена';
					
				} else
				
				    $text_str    = 'Недостаточно прав';
				
				$editMode = 0;
				
				} else {
				
				$news_item = new News_Item();				
				$news_item->Create($editCategory, $title, $mes, $mesFull, $editInfo['vote'], $editInfo['discus']);
				
				$state     = 'success';
				$text_str = 'Новость добавлена';
				
				}				
			}
			
			include $this->GetView('news_admin_mess.html');
			$error = ob_get_clean();
			
		} elseif (isset($_GET['delete'])) {

		    $news_item = new News_Item((int)$_GET['delete']);
			$news_item->Delete();
			
			header("Location: ".$this->work_link."ok");
			
		} elseif (isset($_GET['edit'])) {
			
			$editorTitle  = 'Обновить новость';
			$editorButton = 'Изменить';
			
			$news_item = new News_Item((int)$_GET['edit']);
			
			if (!$news_item->Exist() ) return ''; 
			
			$editInfo = $news_item->getInfo();
			
			$editMode         = $editInfo['id'];
			$editCategory     = $editInfo['category_id'];
			$editTitle        = TextBase::HTMLDestruct($editInfo['title']);
			$editMessage      = TextBase::HTMLDestruct($editInfo['text']);
			$editMessage_Full = TextBase::HTMLDestruct($editInfo['text_full']);
			
		}

	ob_start();
	
	$cat_list = CategoryManager::GetList($editCategory);
 	
	include $this->GetView('news_add.html');
				  
	return ob_get_clean();

	}

	public function ShowNewsListing($list = 1) {
	global $bd_names,$config;

    $sql = '';
    if ( $this->category_id > 0 ) $sql = ' WHERE category_id='.$this->category_id.' ';	

	$list = (int) $list;
	
	if ( $list <= 0 ) $list = 1; 
 	
	if ( $this->category_id > 0 )
	   $category = CategoryManager::GetNameByID($this->category_id);
	else
	   $category = 'Последние новости';
     
	$category_id = $this->category_id;
	$category_link = Rewrite::GetURL(array('category', $category_id), array('', 'cid'));
	
	ob_start(); include $this->GetView('news_header.html');					  
	$html_news = ob_get_clean();
    $news_pnum  = $config['news_by_page'];
	
	$result = BD("SELECT COUNT(*) FROM `{$bd_names['news']}`".$sql);
	$line = mysql_fetch_array($result, MYSQL_NUM );
		  
	$newsnum = $line[0];	
	if (!$newsnum) {
	
	$html_news .= $this->ShowPage('news_empty.html');
	return $html_news;	
	}
	
	$result = BD("SELECT id FROM `{$bd_names['news']}`".$sql."ORDER by time DESC LIMIT ".($news_pnum*($list-1)).",".$news_pnum);

	if ( mysql_num_rows( $result ) != 0 ) {

		  while ( $line = mysql_fetch_array( $result , MYSQL_NUM ) ) {
		  
		         $news_item = new News_Item($line[0], $this->st_subdir);
				 
		         $html_news .= $news_item->Show(); 
				 unset($news_item);				 
		  }

	$html_news .= $this->arrowsGenerator($this->work_link, $list, $newsnum, $news_pnum, 'news');		  		  
	}		
	return $html_news;	
	}	
}
?>