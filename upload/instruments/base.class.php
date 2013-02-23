<?php

define('PROGNAME', 'WebMCR 2.0');
define('FEEDBACK', '<a href="http://drop.catface.ru/index.php?nid=17">'.PROGNAME.'</a> &copy; 2013 NC22');  

/* TODO обобщенная модель для удаления \ проверки существования объекта */

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
?>