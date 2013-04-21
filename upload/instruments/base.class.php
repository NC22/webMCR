<?php
define('PROGNAME', 'WebMCR 2.1');
define('FEEDBACK', '<a href="http://drop.catface.ru/index.php?nid=17">'.PROGNAME.'</a> &copy; 2013 NC22');  

/* TODO обобщенная модель для удаления \ проверки существования объекта */

class ItemType { 

	const News = 1;
	const Comment = 2;
	const Skin = 3;	
}

Class TextBase {

	public static function SQLSafe($text) {
    global $link;
	  
	  return mysql_real_escape_string($text, $link);

	}
	
    public static function HTMLDestruct($text) {
	
	  return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	
	}	
	
	public static function HTMLRestore($text) {

	  return html_entity_decode($text, ENT_QUOTES, 'UTF-8');

	}
    
    public static function StringLen($text) {
     
      return mb_strlen($text, 'UTF-8');

    }	

	public static function CutString($text, $from = 0, $to = 255) {
	
	  return mb_substr($text, $from, $to, 'UTF-8');
	
	}

	public static function CutWordWrap($text) {
	
		return str_replace(array("\r\n", "\n", "\r"),'',$text);
	
	}
	
	/* WordWrap - разбиение непрерывного текстового сообщения пробелами	*/
	
	public static function WordWrap($text, $width = 60, $break = "\n") {

	   return preg_replace('#([^\s]{'. $width .'})#u', '$1'. $break , $text);
		   
	}	
}

Class EMail {
const ENCODE = 'utf-8';
	
	public function Send($mail_to, $subject, $message) {
	global $config;	
	
		$headers = array();
		$headers[] = "Reply-To: ".$config['fbackMail'];
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: text/html; charset=\"".self::ENCODE."\"";
		$headers[] = "Content-Transfer-Encoding: 8bit";
		$headers[] = "From: \"".$config['fbackName']."\" <".$config['fbackMail'].">";
		$headers[] = "To: ".$mail_to." <".$mail_to.">";
		$headers[] = "X-Priority: 3";	
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		$headers = implode("\r\n", $headers);

		$subject = '=?'.self::ENCODE.'?B?'.base64_encode($subject).'?=';
		
		return ($config['smtp'])? self::smtpmail($mail_to, $subject, $message, $headers) : mail($mail_to, $subject, $message, $headers);
	}
	
	private function smtpmail($mail_to, $subject, $message, $headers) {
	global $config;	
		
		$send = "Date: ".date("D, d M Y H:i:s")." UT\r\n";
		$send .= "Subject: {$subject}\r\n";			
		$send .= $headers."\r\n\r\n".$message."\r\n";

		if( !$socket = fsockopen($config['smtpHost'], $config['smtpPort'], $errno, $errstr, 10) ) {
			vtxtlog('[SMPT] '.$errno." | ".$errstr);
			return false;
		}
		
		stream_set_timeout($socket, 10);
		
		if (!self::server_action($socket, false, "220") or
			!self::server_action($socket, $config['smtpHello']." " . $config['smtpHost'] . "\r\n", "250", 'Приветствие сервера недоступно')) 
				return false;
			
		if (!empty($config['smtpUser']))
			if (!self::server_action($socket, "AUTH LOGIN\r\n", "334", 'Нет ответа авторизации') or
				!self::server_action($socket, base64_encode($config['smtpUser']) . "\r\n", "334", 'Неверный логин авторизации') or
				!self::server_action($socket, base64_encode($config['smtpPass']) . "\r\n", "235", 'Неверный пароль авторизации')) 
					return false;
				
		if (!self::server_action($socket, "MAIL FROM: <".$config['smtpUser'].">\r\n", "250", 'Ошибка MAIL FROM') or
			!self::server_action($socket, "RCPT TO: <" . $mail_to . ">\r\n", "250", 'Ошибка RCPT TO') or
			!self::server_action($socket, "DATA\r\n", "354", 'Ошибка DATA') or
			!self::server_action($socket, $send."\r\n.\r\n", "250", 'Ошибка сообщения')) 
				return false;
		
		self::server_action($socket, "QUIT\r\n"); 
		return true;
	}

	private function server_action($socket, $command = false, $correct_response = false, $error_mess = false, $line = __LINE__)	{
		
		if ($command) fputs($socket, $command);		
		if ($correct_response) { 
		
			$server_response = '';
			while (substr($server_response, 3, 1) != ' ') {
				if ($server_response = fgets($socket, 256)) continue;

				if ($error_mess) vtxtlog('[SMPT] '.$error_mess.' Line: '.$line);			
				return false;
			}
			$code = substr($server_response, 0, 3);
			if ($code == $correct_response) return true;
		}
		
		if ($error_mess) vtxtlog('[SMPT] '.$error_mess.' | Code: '.$code.' Line: '.$line);	
		fclose($socket);
		
		if ($correct_response) return false; return true;
	}
}

Class Message {

	/*	 
	 Comment - Валидация короткого сообщения, для хранения в БД и вывода на странице

	 Обрезать до 255 символов
	 Расформировать HTML
	 Заменить все переносы строк на <br>
	 Удалить оставшиеся символы переноса строки
	 
	*/

	public static function Comment($text) {
       
	   $text = TextBase::CutString(TextBase::HTMLDestruct($text));
	   $text = TextBase::CutWordWrap(nl2br($text));
	   
	  return TextBase::SQLSafe($text);      
	}
	
	/*
	
	 RestoreCom - Привести короткое сообщение в редактируемый вид
	
	*/
	
	public static function RestoreCom($string){

      return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	
    }
	
	// TODO BBEncode
	
	public static function BBDecode($text) {

	    $text = preg_replace("/\[b\](.*)\[\/b\]/Usi", "<b>\\1</b>", $text);
        $text = preg_replace("/\[u\](.*)\[\/u\]/Usi", "<u>\\1</u>", $text);
        $text = preg_replace("/\[i\](.*)\[\/i\]/Usi", "<i>\\1</i>", $text);
        $text = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+)\](.*)\[\/color\]/Usi", "<span style=\"color:\\1\">\\2</span>", $text);
	    $text = preg_replace("/\[url=(?:&#039;|&quot;)http:\/\/([^<]+)(?:&#039;|&quot;)](.*)\[\/url]/Usi", "<a href=\"http://\\1\">\\2</a>", $text, 3);
	    
		$tmp = $text;
		
		while(strcmp($text=preg_replace("/\[quote=(?:&#039;|&quot;)(.*)(?:&#039;|&quot;)\](.+?)\[\/quote\]/Uis","<div class=\"comment-quote\"><div class=\"comment-quote-a\">\\1 сказал(a):</div><div class=\"comment-quote-c\">\\2</div></div>",$tmp),$tmp)!=0) 
		 $tmp = $text; 

		return $text;
	}
	
}

Class Menager {
private $style;

	public function Menager($style = false) {
	global $site_ways;
	
		$this->style = (!$style)? MCR_STYLE : $style;
		
	}

	public static function ShowStaticPage($page) {
	global $config;
	
		ob_start(); 
		
		include $page;
		
		return ob_get_clean(); 	
	}

    public function arrowsGenerator($link, $curpage, $itemsnum, $per_page, $prefix = 'news') { 

	  $numoflists = ceil($itemsnum / $per_page);
	  $arrows = '';
	  
			  if ($numoflists > 10 and $curpage > 4) {
			  
				$showliststart = $curpage - 4;
				$showlistend   = $curpage + 5;
				
				if ($showliststart < 1) $showliststart = 1;
				
				if ($showlistend > $numoflists) $showlistend = $numoflists;
				
			  } else {
			  
				$showliststart = 1;
				
				if ($numoflists < 10 ) $showlistend = $numoflists;
				else                   $showlistend = 10;
			  
			  }
			 
			 ob_start();	
			 
			  if ($numoflists>1) {
	 
				if ($curpage > 1) { 
				
				  if ($curpage-4 > 1) { $var = 1; $text = '<<'; include $this->style.$prefix.'_list_item.html'; } 
				  
				  $var = $curpage-1; $text = '<'; include $this->style.$prefix.'_list_item.html'; 
				
				}
				
					for ($i=$showliststart;$i<=$showlistend;$i++) {
					
					$var  = $i; 
					$text = $i;
					
						if ($i == $curpage) include $this->style.$prefix.'_list_item_selected.html'; 
						else			    include $this->style.$prefix.'_list_item.html'; 
						
					}
					
				if ($curpage < $numoflists) { 
				
				  $var = $curpage+1; $text = '>'; include $this->style.$prefix.'_list_item.html'; 
				  
				  if ($curpage+5 < $numoflists) { $var = $numoflists; $text = '>>'; include $this->style.$prefix.'_list_item.html'; } 
				
				}
				
			  }
			  
		$arrows = ob_get_clean();
		
		if ( $arrows ) {
		
			ob_start(); 
			  
			include $this->style.$prefix.'_list.html';	
			  
			return ob_get_clean();			  
		}
		
	return '';
	}
}

class ItemLike {
private $id;
private $type;
private $user_id;

private $bd_item;
	
	public function ItemLike($item_type, $item_id, $user_id) {
	global $bd_names;
	
	$this->id = false;
	
		switch ($item_type) {
			case ItemType::News: $this->bd_content = $bd_names['news']; break;
			case ItemType::Comment: $this->bd_content = $bd_names['comments']; break; 
			default: return false; break;
		}
		
	$this->db = $bd_names['likes'];	
	
	$this->id		= (int) $item_id;
	$this->type		= (int) $item_type;
	$this->user_id	= (int) $user_id;	
	}
	
	public function Like($dislike = false) {

		$var = (!$dislike)? 1 : -1;

		$result = BD("SELECT `var` FROM `{$this->db}` WHERE `user_id` = '".$this->user_id."' AND `item_id` = '".$this->id."' AND `item_type` = '".$this->type."'"); 
		
		if ( !mysql_num_rows( $result ) ) { 
		
			BD("INSERT INTO `{$this->db}` (`user_id`, `item_id`, `item_type`, `var`) VALUES ('".$this->user_id."', '".$this->id."', '".$this->type."', '".$var."')");
		
			if (!$dislike) 
				BD("UPDATE `{$this->bd_content}` SET `likes` = `likes` + 1 WHERE `id` = '".$this->id."'");					
			else 	     
				BD("UPDATE `{$this->bd_content}` SET `dislikes` = `dislikes` + 1 WHERE `id` = '".$this->id."'");
		
		return 1;
		
		} else {
			
			$line = mysql_fetch_array( $result, MYSQL_NUM );
			
			if ((int)$line[0] == (int)$var) return 0; 
			
			BD("UPDATE `{$this->db}` SET `var` = '".$var."' WHERE `user_id` = '".$this->user_id."' AND `item_id` = '".$this->id."' AND `item_type` = '".$this->type."'");		
			
			if (!$dislike) 
				BD("UPDATE `{$this->bd_content}` SET `likes` = `likes` + 1, `dislikes` = `dislikes` - 1  WHERE `id` = '".$this->id."'");
			else 
				BD("UPDATE `{$this->bd_content}` SET `likes` = `likes` - 1, `dislikes` = `dislikes` + 1 WHERE `id` = '".$this->id."'");			
		
		return 2;		
		}
	}
}

Class Menu {
private $menu_items;
private $style;

    public function Menu($style = false) {
	global $site_ways;
	
		$this->style = (!$style)? MCR_STYLE : $style;
	}

	public function Show() {
    $menu_items = '';    

        for ($i=0;$i < sizeof($this->menu_items);$i++) {

			$button_name  = $this->menu_items[$i]['name'];
			$button_url   = $this->menu_items[$i]['url'];
			$button_class = $this->menu_items[$i]['class'];
            
		    ob_start(); include ($this->style.'menu_item.html');
		    $menu_items .= ob_get_clean();
						
		}

		ob_start(); include ($this->style.'menu.html');
		return ob_get_clean();

	}

    public function AddItem($name,$url,$active = false) {

    $new_item_key = sizeof($this->menu_items);

     $this->menu_items[$new_item_key]['name']  = $name;
     $this->menu_items[$new_item_key]['url']   = $url;
     $this->menu_items[$new_item_key]['class'] = ($active)? 'active' : 'not_active';
   
    return $new_item_key;
    }

    public function SetItemActive($item_key) {

      $this->menu_items[$item_key]['class'] = 'active';

        for ($i=0;$i < sizeof($this->menu_items);$i++) 
          if ($i != $item_key and $this->menu_items[$i]['class'] == 'active')
            $this->menu_items[$i]['class'] = 'not_active';

    }
	
}