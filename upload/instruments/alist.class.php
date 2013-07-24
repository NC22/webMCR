<?php
if (!defined('MCR')) exit;

class ThemeManager extends View {

	private $work_skript;
	private $theme_info_cache;
	
	const sign_file = 'sign.txt';

	/** @const */
	public static $true_info = array (
		
		'name',
		'version',
		'author',	
		'about',	 
		'date',	 
		'web',
		'license',	
	);	

    public function ThemeManager($style_sd = false, $work_skript = '?mode=control') { 
		
		/*	Show subdirs used: /admin */
		
		parent::View($style_sd);
		
		$this->theme_info_cache = null;
		$this->work_skript = $work_skript;	
	}
	
	public function isThemesEnabled() {
	global $config;
	
		if ($this->theme_info_cache === 'depricated') return false;
		
		if (!isset($config['s_theme']) or file_exists(MCR_STYLE . 'index.html') ) {
		
		$this->theme_info_cache = 'depricated';
		return false;
		}
		
		return true;		
	}
	
	public function GetThemeSelectorOpt() {
	global $config;
	
		if (!$this->isThemesEnabled()) return '<option value="-1">'.lng('NOT_SET').'</option>';

		$theme_list = $this->GetThemeList();
			
		$html_list = '';

		for($i=0; $i < sizeof($theme_list); $i++) 
			
			$html_list .= '<option value="'.$theme_list[$i]['id'].'" '.(($theme_list[$i]['id'] === $config['s_theme'])? 'selected' : '' ).'>'.$theme_list[$i]['id'].'</option>';
				
		return $html_list;
	}
	
	public function ShowThemeInfoFields($buffer = false) {
	
	if (!$this->isThemesEnabled()) return '';
	
	$theme_list = $this->GetThemeList();
	
	if ($buffer) ob_start(); 
	
		foreach ($theme_list as $key => $theme_info)
			
			include $this->GetView('admin/theme_item.html'); 

    if ($buffer) return ob_get_clean();	
	}	
	
	public function GetThemeList() {

	if ($this->theme_info_cache != null ) return $this->theme_info_cache;
	
	if (!$this->isThemesEnabled()) return $this->theme_info_cache; 
	
	$this->theme_info_cache = array();		
	
	if ($theme_dir = opendir(MCR_STYLE)) { 

       while (false !== ($theme = readdir($theme_dir))) {
	   
			if ($theme == '.' or  $theme == '..' or !file_exists(MCR_STYLE. $theme . '/' . self::sign_file)) 
			
				continue;
				
            else 
			
				$this->theme_info_cache[] = self::GetThemeInfo($theme);
				
		}

       closedir($theme_dir);  
	}
	
	$this->theme_info_cache = $theme_list;
	
	return $this->theme_info_cache;
	}	
	
	public static function GetThemeInfo($theme_id) {
		
		$theme_info = array();
		
		$theme_info['id'] = $theme_id;		
		$sign_file = MCR_STYLE . $theme_id . '/' . self::sign_file;
		
			if (filesize($sign_file) > 128 * 1024) return $theme_info;
		
		$theme_info_txt =  file_get_contents($sign_file);
		$theme_info_txt =  explode (';', $theme_info_txt);
			
			if (!sizeof($theme_info_txt)) return $theme_info;
			
			for($i=0; $i < sizeof($theme_info_txt); $i++) {
			
				for ($b=0; $b < sizeof(self::$true_info); $b++) {
				
					if ( !substr_count($theme_info_txt, self::$true_info[$b])) 
						
						continue; 
						
						$info_value =  explode ('=', $theme_info_txt[$i]);
						
						if (sizeof($info_value) == 2)
						
							$theme_info[self::$true_info[$b]] = $info_value[1];					
				}
			}
		
		return $theme_info;	
	}
}

class ControlManager extends Manager {
private $work_skript;

    public function ControlManager($style_sd = false, $work_skript = '?mode=control') { 
		
		/*	Show subdirs used: /admin */
		
		parent::Manager($style_sd);
		
		$this->work_skript = $work_skript;	
	}

	public function ShowUserListing($list = 1, $search_by = 'name', $input = false) {
	global $bd_users,$bd_names;

		$input = TextBase::SQLSafe($input);
	
	    if ($input == 'banned') $input = 0;
	
	    if ($search_by == 'name') $result = BD("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE {$bd_users['login']} LIKE '%$input%' ORDER BY {$bd_users['login']} LIMIT ".(10*($list-1)).",10"); 
    elseif ($search_by == 'none') $result = BD("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` ORDER BY {$bd_users['login']} LIMIT ".(10*($list-1)).",10"); 
	elseif ($search_by == 'ip'  ) $result = BD("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE {$bd_users['ip']} LIKE '%$input%' ORDER BY {$bd_users['login']} LIMIT ".(10*($list-1)).",10"); 
	elseif ($search_by == 'lvl' ) {
	
		$result = BD("SELECT `id` FROM `{$bd_names['groups']}` WHERE `lvl`='$input'");
		
		$id_group  = mysql_fetch_array( $result, MYSQL_NUM );    
	    $input = $id_group[0];
		
	    $result = BD("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['group']}` = '$input' ORDER BY {$bd_users['login']} LIMIT ".(10*($list-1)).",10"); 
	}
        
		ob_start(); 		

	          $resnum =  mysql_num_rows( $result );	
	    if ( !$resnum ) { include $this->GetView('admin/user_not_found.html'); return ob_get_clean(); }  
		
        include $this->GetView('admin/user_find_header.html'); 
  
		while ( $line = mysql_fetch_array( $result, MYSQL_NUM ) ) {
		
            $inf_user = new User($line[0],$bd_users['id']);
			
            $user_name = $inf_user->name();
            $user_id   = $inf_user->id();
            $user_ip   = $inf_user->ip();
            $user_lvl  = $inf_user->getGroupName();
			$user_lvl_id = $inf_user->group();
			
            unset($inf_user);
			
            include $this->GetView('admin/user_find_string.html'); 
        } 
		
		include $this->GetView('admin/user_find_footer.html'); 

        $html = ob_get_clean();

	    if ($search_by == 'name') $result = BD("SELECT COUNT(*) FROM `{$bd_names['users']}` WHERE {$bd_users['login']} LIKE '%$input%'");
	elseif ($search_by == 'none') $result = BD("SELECT COUNT(*) FROM `{$bd_names['users']}`");
	elseif ($search_by == 'ip'  ) $result = BD("SELECT COUNT(*) FROM `{$bd_names['users']}` WHERE {$bd_users['ip']} LIKE '%$input%'");
	elseif ($search_by == 'lvl' ) $result = BD("SELECT COUNT(*) FROM `{$bd_names['users']}` WHERE `{$bd_users['group']}`='$input'");
		
		$line = mysql_fetch_array($result);
		$html .= $this->arrowsGenerator($this->work_skript, $list, $line[0], 10);
      
     return $html;
	}
	
    public function ShowServers($list) { 
    global $bd_names;

    ob_start(); 	
	
    include $this->GetView('admin/servers_caption.html');
	
	// TODO increase priority by votes
	
    $result = BD("SELECT * FROM `{$bd_names['servers']}` ORDER BY priority DESC LIMIT ".(10*($list-1)).",10");  
    $resnum = mysql_num_rows( $result );
	
	if ( !$resnum ) { include $this->GetView('admin/servers_not_found.html'); return ob_get_clean(); }  
		
	include $this->GetView('admin/servers_header.html'); 
		
		while ( $line = mysql_fetch_array( $result ) ) {
		
            $server_name     = $line['name'];
			$server_address  = $line['address'];
			$server_info     = $line['info'];
			$server_port     = $line['port'];
	        $server_method   = '';
			
			switch ((int)$line['method']) {
			case 0: $server_method = 'Simple query'; break;
			case 1: $server_method = 'Query'; break; 
			case 2: $server_method = 'RCON'; break;
                        case 3: $server_method = 'JSONAPI'; break;
			}			
			$server_id       = $line['id'];
		
		include $this->GetView('admin/servers_string.html');         
        }
        
	include $this->GetView('admin/servers_footer.html'); 
	$html = ob_get_clean();
	
		$result = BD("SELECT COUNT(*) FROM `{$bd_names['servers']}`");
		$line = mysql_fetch_array($result); 
		$resnum = $line[0];
					  		  
		$html .= $this->arrowsGenerator($this->work_skript, $list, $line[0], 10);

    return $html;
    }
	
    public function ShowIpBans($list) {
    global $bd_names;

    RefreshBans();

    ob_start(); 	
	
    include $this->GetView('admin/ban_ip_caption.html');
	
    $result = BD("SELECT * FROM `{$bd_names['ip_banning']}` ORDER BY ban_until DESC LIMIT ".(10*($list-1)).",10");  
    $resnum = mysql_num_rows( $result );
	
	if ( !$resnum ) { include $this->GetView('admin/ban_ip_not_found.html'); return ob_get_clean(); }  
		
	include $this->GetView('admin/ban_ip_header.html'); 
		
		while ( $line = mysql_fetch_array( $result ) ) {
		
             $ban_ip    = $line['IP'];
             $ban_start = $line['time_start'];
             $ban_end   = $line['ban_until'];
			 $ban_type  = $line['ban_type'];
			 $ban_reason  = $line['reason'];			 
			 
		     include $this->GetView('admin/ban_ip_string.html'); 
        
        }
        
	include $this->GetView('admin/ban_ip_footer.html'); 
	$html = ob_get_clean();
	
		$result = BD("SELECT COUNT(*) FROM `{$bd_names['ip_banning']}`");
		$line = mysql_fetch_array($result); 
		$resnum = $line[0];
					  		  
		$html .= $this->arrowsGenerator($this->work_skript, $list, $line[0], 10);

    return $html;
    }
}
?>