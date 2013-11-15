<?php
if (!defined('MCR')) exit;

Class Server extends Item {

private $address;
private $port; // port for connection to monitoring service
private $method; 
			
private $name;			
private $slots;			
private $info;
private $numpl;
private $online;

private $refresh;	
private $rcon; 
private $s_user; 

	public function Server($id = false, $style_sd = false) {
	global $bd_names;
	
		parent::__construct($id, ItemType::Server, $bd_names['servers'], $style_sd);
        
		if (!$this->id) return false;
		
		$result = BD("SELECT online, address, port, name, numpl, service_user, slots, info, refresh_time, method, rcon FROM `".$this->db."` WHERE id='".TextBase::SQLSafe($this->id)."'");
		if ( mysql_num_rows( $result ) != 1 ) { $this->id = false; return false; }
			
		$line = mysql_fetch_array($result, MYSQL_ASSOC);

		$this->address = $line['address'];
		$this->port    = (int)$line['port'];
		$this->method  = (int)$line['method']; 
			
		$this->name    = $line['name'];			
		$this->slots   = (int)$line['slots'];			
		$this->info    = $line['info'];
		$this->numpl   = (int)$line['numpl'];
		$this->online  = ($line['online'])? true : false;

		$this->refresh = (int)$line['refresh_time'];
		$this->rcon    = (!strlen($line['rcon']))? false : $line['rcon'];  
		
		$this->s_user = (!strlen($line['service_user']))? false : $line['service_user'];  
		
	return true;			
	}
	
	public function Create($address, $port, $method = 0, $rcon = false, $name = '', $info = '', $s_user = false) {
                
		if ($this->Exist()) return 0; 
		
		if (!$address or !TextBase::StringLen($address)) return false;
				
			$method = (int)$method;
		if ($method < 0 or $method > 3) $method = 0;
		
		if ($rcon and !($method == 2 or $method == 3)) $rcon = '';
		
		if (!$rcon and ( $method == 2 or $method == 3)) return 2;
		if (!$s_user and $method == 3) return 3;
		
		$port = (int) $port;
		if (!$port) $port = 25565;

		if ( BD("insert into `".$this->db."` ( address, port, info, name, method, service_user, rcon ) values ('".TextBase::SQLSafe($address)."', '".TextBase::SQLSafe($port)."', '".TextBase::SQLSafe($info)."' , '".TextBase::SQLSafe($name)."', '".TextBase::SQLSafe($method)."', '".TextBase::SQLSafe($s_user)."', '".TextBase::SQLSafe($rcon)."' )") ) 
          
		  $this->id = mysql_insert_id();
		  
		else return 4;
		
		$this->address = $address;
		$this->port    = $port; 
		$this->method  = $method; 
		$this->info    = $info;			
		$this->name    = $name;			
		$this->rcon    = $rcon;  		
		
		return 1; 
	}
	
   public function SetConnectMethod($method = 0, $rcon = '', $s_user = '') {
	
	if (!$this->Exist()) return false;
		    
		$method = (int)$method;
	if ($method < 0 or $method > 3) $method = 0;
	
	if ($rcon and !( $method == 2 or $method == 3)) $rcon = '';
	if (!$rcon and ( $method == 2 or $method == 3)) return false;
	if (!$s_user and $method == 3 ) return false;
		
	BD("UPDATE `".$this->db."` SET `method`='".TextBase::SQLSafe($method)."',`rcon`='".TextBase::SQLSafe($rcon)."',`service_user`='".TextBase::SQLSafe($s_user)."' WHERE `id`='".$this->id."'"); 	
	
	$this->method = $method;
	$this->rcon   = $rcon;
   }   
   
   public function SetConnectWay($address, $port) {
	
	if (!$this->Exist()) return false;	
	
	if (!$address or !TextBase::StringLen($address)) return false;
	
	$port = (int) $port;
	if (!$port) $port = 25565;	
	
	BD("UPDATE `".$this->db."` SET `address`='".TextBase::SQLSafe($address)."',`port`='".TextBase::SQLSafe($port)."' WHERE `id`='".$this->id."'"); 
	
	$this->address = $address;
	$this->port    = $port;
	return true;
   }
   
   public function SetText($var, $field = 'name') {
	
	if (!$this->Exist()) return false;
	else if (!$field == 'name' and !$field == 'info') return false;
	
	if (!$var or !TextBase::StringLen($var)) return false;
	
	BD("UPDATE `".$this->db."` SET `".TextBase::SQLSafe($field)."`='".TextBase::SQLSafe($var)."' WHERE `id`='".$this->id."'"); 
	
	if ($field == 'name') $this->name = $var;
	else  $this->info = $var;
   }  
   	
	private function IsTimeToUpdate() {

	if (!$this->Exist()) return false;
	
		$result = BD("SELECT last_update FROM `".$this->db."` WHERE id='".$this->id."' AND last_update<NOW()-INTERVAL ".TextBase::SQLSafe($this->refresh)." MINUTE"); 

	    if ( mysql_num_rows( $result ) == 1 ) return true;
		else return false;
		
	}
	
	public function UpdateState($extra = false) {
    global $config;
    
	if ((!$extra and !$this->IsTimeToUpdate()) or !$this->Exist()) return;
	
	$this->online = false;
	$users_list = NULL;
	
	if (empty($this->address)) {	
	 BD("UPDATE `".$this->db."` SET online='0',last_update=NOW() WHERE id='".$this->id."'"); 
	 return;
    }
	
	BD("UPDATE `".$this->db."` SET last_update=NOW() WHERE id='".$this->id."'"); 
        switch ($this->method) {
		
// RCON Connect 
           
        case 2:
            
		loadTool('rcon.class.php');
		
		try	{
		
			$rcon = new MinecraftRcon;
			$rcon->Connect( $this->address, $this->port, $this->rcon);
			$str = $rcon->Command('list');
			
		} catch( MinecraftRconException $e ){
		
			if ($e->getMessage() == 'Server offline') {
			   BD("UPDATE `".$this->db."` SET online='0' WHERE id='".$this->id."'"); 
			   return;
			}
		}

		$str = str_replace(array("\r\n", "\n", "\r"),'', $str);
		$names = explode(', ',substr($str, 19)); 
		
		if (!empty($names)) for($i=0;$i<sizeof($names);$i++) trim($names[$i]); 
		if (!$names[0]=='') $users_list = $names;  
		
        break;
        case 3:        

		loadTool('json_api.php', 'bukkit/');

		$salt = sqlConfigGet('json-verification-salt');
	
		if (!$salt) { 
		
			$salt = md5(rand(1000000000, 2147483647).rand(1000000000, 2147483647));
			sqlConfigSet('json-verification-salt', $salt); 	
		}
		
			if (!extension_loaded("cURL")) { vtxtlog('[monitoring.class.php] cURL module is required'); return; }
		
            $api = new JSONAPI($this->address, $this->port, $this->s_user, $this->rcon, $salt); // ToDo rewrite / delete . curl is custom module
                
            $apiresult = $api->call(array("getPlayerLimit","getPlayerCount"), array(NULL,NULL));
                
            if (!$apiresult) {
               
			   BD("UPDATE `".$this->db."` SET online='0' WHERE id='".$this->id."'"); 
               return;
            }
				
            $full_state = array('numpl'=>$apiresult["success"][1]["success"],'maxplayers'=>$apiresult["success"][0]["success"] );
        break;
			
// query, simple query	
	
        default :
	
		loadTool('query.function.php');
		 
		$full_state = ($this->method == 1)? mcraftQuery($this->address, $this->port ) : mcraftQuery_SE($this->address, $this->port );		 
		if (empty($full_state) or isset($full_state['too_many'])) {
		
		   BD("UPDATE `".$this->db."` SET online='".((isset($full_state['too_many']))? '1' : '0')."' WHERE id='".$this->id."'"); 
		   
		   $this->online = (isset($full_state['too_many']))? true : false;
		   return;
		}
		elseif (!empty($full_state['players'])) $users_list = $full_state['players']; 
		
	}
        
	$this->online = true;	
	  
	$system_users = '';
	$numpl = (!empty($full_state['numpl']))? $full_state['numpl'] : 0;
	
	if ($users_list) {
		
		$numpl = sizeof($users_list);
		
	    if ($numpl == 1) $system_users = $users_list[0];
	    else {
		
			for($i=0; $i < $numpl; $i++) {
				if ($i == 0) 	$system_users .= $users_list[$i];
				else 			$system_users .= ','.$users_list[$i];
			}		
		}			 
	}
	
	$this->slots = (!empty($full_state))? $full_state['maxplayers'] : -1;
	$this->numpl = $numpl;
	
	if (!empty($full_state))	// name='".$full_state['hostname']."'
	  BD("UPDATE `".$this->db."` SET numpl='".TextBase::SQLSafe($numpl)."',slots='".TextBase::SQLSafe($full_state['maxplayers'])."',players='".TextBase::SQLSafe($system_users)."',online='1' WHERE id='".$this->id."'"); 		 
    else
  	  BD("UPDATE `".$this->db."` SET numpl='".TextBase::SQLSafe($numpl)."',slots='-1',players='".TextBase::SQLSafe($system_users)."',online='1' WHERE id='".$this->id."'"); 		 
	
	}	
	
	public function GetPlayers() {
	
	if (!$this->Exist()) return false;
	
			$result = BD("SELECT players, numpl FROM `".$this->db."` WHERE id='".$this->id."'");
			$players = mysql_fetch_array($result, MYSQL_ASSOC);
			$list    = $players['players'];
			$numpl   = (int)$players['numpl'];
			
			if (!strlen($list) and !$numpl) return array("Сервер пуст", 0);
			
			if (!sizeof(explode(',',$list)) and !$numpl) return array("Сервер пуст", 0);
						                           else  return array($list, $numpl);
    }
	
	public function SetVisible($page,$state) {
	
	if (!$this->Exist()) return false;
	
	    $page = ServerManager::getPageName($page);
		if (!$page) return false;
		
		$state = ($state)? 1 : 0;
		
		BD("UPDATE `".$this->db."` SET `$page`='$state' WHERE `id`='".$this->id."'"); 
	}
   
   public function GetVisible($param) {

		if (!$this->Exist()) return -1;	
		
		     $param = ServerManager::getPageName($param);
		if (!$param) return false;
		
		$result = BD("SELECT `$param` FROM `".$this->db."` WHERE `id`='".$this->id."'");
		
		if (mysql_num_rows( $result ) == 1) {
		
			$line  = mysql_fetch_array($result, MYSQL_NUM );
			$value = ((int)$line[0])? true : false;
			
			return $value;
			
		} else return -1;		
   }
   
   public function SetRefreshTime($newTimeout) {
	
	if (!$this->Exist()) return false;
	
	    $newTimeout = (int)$newTimeout;
		if ($newTimeout < 0) $newTimeout = 0;
		
		BD("UPDATE `".$this->db."` SET `refresh_time`='".TextBase::SQLSafe($newTimeout)."' WHERE `id`='".$this->id."'"); 
		
	$this->refresh = $newTimeout;	
   return true;		
   }
   
   public function SetPriority($new) {
	
	if (!$this->Exist()) return false;
	
	    $new = (int)$new;
		if ($new < 0) $new = 0;
		
		BD("UPDATE `".$this->db."` SET `priority`='".TextBase::SQLSafe($new)."' WHERE `id`='".$this->id."'"); 
		
	return true;
   }  
   
   public function GetPriority() {
   
        if (!$this->Exist()) return false;

		$result = BD("SELECT `priority` FROM `".$this->db."` WHERE `id`='".$this->id."'");
		
		if (mysql_num_rows( $result ) == 1) {
		
			$line  = mysql_fetch_array($result, MYSQL_NUM );
		    return (int)$line[0];
			
		} else return false;		
   }
   
	public function ShowHolder($type = 'side', $server_prefix = '') {
	
		if (!ServerManager::getPageName($type)) return false;
	
        ob_start();	
		
		$server_name   = $this->name;
		$server_info   = $this->info;  // this->address - фактический адресс
		$server_id     = $this->id; 
		$server_pid    = $server_prefix.$server_id;
		$server_numpl  = $this->numpl;
		$server_slots  = $this->slots;
		
		if ((int)$this->slots != -1)
			$server_pl_inf = $this->numpl.'/'.$this->slots;
		else 
			$server_pl_inf = $this->numpl;		
				
    	switch ($type) {		
		case 'mon':            
		case 'side': include $this->GetView('serverstate_'.$type.'.html');	break;
		case 'game':
		
		if ( $this->online ) include $this->GetView('serverstate_'.$type.'_online.html');  
		else include $this->GetView('serverstate_'.$type.'_offline.html');	
		
        break;		
		default: return false; break;
		}
        
		return ob_get_clean();	
    }
	
	public function ShowInfo() {
	global $ajax_message;
	
	  $ajax_message = array('code' => 0, 
	                        'message' => '',
							'info' => '',
							'address' => '',
							'port' => 0,
 							'online' => 0,
							'numpl' => 0,
							'pl_array' => '',
							'slots' => 0,
							'name' => '',
							'id' => 0,
							);

      if (!$this->id) aExit(1,'server_state');	
	  
	  $ajax_message['id']      = (int)$this->id;		
	  $ajax_message['name']    = $this->name;					
	  $ajax_message['online']  = ($this->online)? 1 : 0;
	  $ajax_message['info']    = $this->info;
	  $ajax_message['address'] = $this->address;
	  $ajax_message['port']    = (int)$this->port;
	  	  
	  if (!$this->online) aExit(2,'server_state'); 
	  
	  $players = $this->GetPlayers();
		
	  $ajax_message['numpl']    = (int)$players[1];
	  $ajax_message['slots']    = (int)$this->slots;
	  $ajax_message['pl_array'] = $players[0];
	 
	  aExit(0,'server_state');
   }
   
	public function getInfo() { 
		if (!$this->Exist()) return false; 
		
		return array (	'id' 		=> $this->id,
						'address' 	=> $this->address,
						'online' 	=> $this->online,
						'refresh' 	=> $this->refresh,
						'port'		=> $this->port,
						's_user'	=> $this->s_user,
						'name'		=> $this->name,
						'method'	=> $this->method,
						'info'		=> $this->info );		
	}
	
   public function info() {
   return $this->info;	
   } 

   public function online () {
   return ($this->online)? true : false ; 
   }
   
   public function name() {
   return $this->name;	
   }   
}

Class ServerManager extends View {

	public function __construct($style_sd = false) { 
	global $site_ways;
	
	   parent::View($style_sd);
	}
	
	public function Show($type = 'side', $update = false) {
	global $bd_names;
	
	         $page = self::getPageName($type);
        if (!$page) return false;
		
		$html_serv = $this->ShowPage('serverstate_'.$type.'_header.html'); 
		
		$result = BD("SELECT `id` FROM `{$bd_names['servers']}` WHERE `$page`=1 ORDER BY priority DESC LIMIT 0,10"); 
			
		if ( mysql_num_rows( $result ) ) { 

		   while ( $line = mysql_fetch_array( $result, MYSQL_NUM ) ) {
					
			$server = new Server($line[0], $this->st_subdir);
			if ($update) $server->UpdateState();
            $html_serv .= $server->ShowHolder($type);
			
			unset($server);
		   }
		   
		} else $html_serv .= $this->ShowPage('serverstate_'.$type.'_empty.html');
		
		$html_serv .= $this->ShowPage('serverstate_'.$type.'_footer.html');	
		
        return $html_serv;		
	}	
	
	public static function getPageName($page) {
	    switch ($page) {
		case 'side': return 'main_page'; break;
		case 'game': return 'news_page'; break; 
		case 'mon':  return 'stat_page'; break;
		default: 	 return false; 		 break;
		}	
	}
}
?>
