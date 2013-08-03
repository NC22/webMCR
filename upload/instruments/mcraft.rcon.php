<?php
if (empty($_POST['command']) and empty($_POST['userlist'])) 
      exit('<script>parent.showResult("command is empty");</script>');
	  
require('../system.php');

BDConnect('mcraft.rcon');

loadTool('rcon.class.php');
loadTool('user.class.php');

MCRAuth::userLoad();	  

if (empty($user) or $user->lvl() < 15) exit;

/* HTML version of GetUserList */

function GetUserListHTML($result) {

$str = trim($result);
$str = str_replace(array("\r\n", "\n", "\r"),'', $str);

$names = explode(', ',substr($str, 19)); 

if (!empty($names)) for($i=0;$i<sizeof($names);$i++) trim($names[$i]); 

if ($names[0]=='') unset($names);

if (empty($names)) return array('<p>Сервер пуст</p>','');

$html = '';
$script = '';

for($i=0;$i<sizeof($names);$i++) {
 
	$script .= 'parent.addNickButton("'.$names[$i].'",'.$i.');'; 
	$html .= '<p><a href="#" id="nickButton'.$i.'">'.$names[$i].'</a></p>'; 
}
 
return array($html,$script);
}

/* Try load connect options */

$game_server = (!empty($_POST['IP']))? $_POST['IP'] : sqlConfigGet('rcon-serv');
if ($game_server == 0) exit('<script>parent.showResult("rcon unconfigured");</script>');

$rcon_port = (!empty($_POST['port']))? (int)$_POST['port'] : (int)sqlConfigGet('rcon-port');
$rcon_pass = (!empty($_POST['pass']))? $_POST['pass'] : sqlConfigGet('rcon-pass');

/* Sync or drop config */

if (!empty($_POST['save'])) {

	sqlConfigSet('rcon-serv',$game_server);
	sqlConfigSet('rcon-pass',$rcon_pass);
	sqlConfigSet('rcon-port',$rcon_port);
	
} else sqlConfigSet('rcon-serv', 0);	
	
	try	{
		$rcon = new MinecraftRcon;
		$rcon->Connect( $game_server, $rcon_port, $rcon_pass);
		
		if (!empty($_POST['userlist'])) {

		 $page = GetUserListHTML($rcon->Command('list'));
		 exit("<script>parent.GetById('users_online').innerHTML = '".$page[0]."'; ".$page[1]."</script>");
		 
		}	
		
		$command = trim($_POST['command']);
		$command = str_replace(array("\r\n", "\n", "\r"),'', $command);
		$command = preg_replace('| +|', ' ', $command);	 
		 
		$str = trim(TextBase::HTMLDestruct($rcon->Command($command)));

		$str = str_replace(array("\r\n", "\n", "\r"),'', $str);

		if (!strncmp($command,'say',3) and strlen($str) > 2) $str = substr($str, 2);
		if (!strncmp(substr($str, 2),'Usage',5)) $str = substr($str, 2);
		 
		$str = str_replace(array(chr(167)), '', $str); 
		
		echo '<script>parent.showResult("'.$str.'");</script>';
	}
	catch( MinecraftRconException $e ) {
		echo '<script>parent.showResult("'.$e->getMessage( ).'");</script>'; 
	}

$rcon->Disconnect( );