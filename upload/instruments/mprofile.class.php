<?php if (!defined('MCR')) exit;

class MProfile extends View {

	private $user;

    public function __construct($user_id, $style_sd = false) { 

		parent::View($style_sd);

		$user_id = (int) $user_id; 
		if (!$user_id) {
		
		$this->user = false;
		return false;
		}
		
		$this->user = new User($user_id);
		
		if (!$this->user->Exist()) { 
		
		unset($this->user);		
		$this->user = false;		
		}
	}

	public static function TimeFrom($time, $time2=-1) {

	$out = "";
	
    $cur_time = ( $time2 == -1 ? date('Y-m-d H:i:s') : $time2 );
	$time_sec = strtotime($cur_time) -  strtotime($time);
 
	if($time_sec < 0) return $out;	
	if($time_sec < 60) $out = "меньше минуты";

	$out .= (int) ($time_sec / 86400); $out .= " д. ";	
	$time_sec = $time_sec % 86400;
	$out .= (int) ($time_sec / 3600); $out .= " ч. ";	
	$time_sec = $time_sec % 3600;
	$out .= (int) ($time_sec / 60); $out .= " мин.";

	return $out;
	}
	
	public function Show() {
	global $user;
	
        if (!$this->user) return false; 
        
        $user_info['name']   = $this->user->name();
        $user_info['group']  = $this->user->getGroupName();
		$user_info['skin']   = $this->user->getSkinLink();
		$user_info['female'] = ($this->user->isFemale())? 1 : 0;
		
		$timeParam = $this->user->gameLoginLast();
		
		$user_info['play_last'] = ($timeParam) ? self::TimeFrom($timeParam) : 'Никогда';
		
		$timeParam = $this->user->getStatisticTime('create_time');
		
		if ($timeParam) 
		
			$user_info['create_time'] = ($config['p_logic'] == 'xenforo' or $config['p_logic'] == 'ipb' or $config['p_logic'] == 'dle')? date('Y-m-d H:i:s', $timeParam) : $timeParam;
		
		else $user_info['create_time'] = 'Неизвестно';
		
	    $timeParam = $this->user->getStatisticTime('active_last');
		
		$user_info['active_last'] = ($timeParam) ? $timeParam : 'Никогда';
		
		$statistic = $this->user->getStatistic();		

		$user_info['comments_num']  = (int) $statistic['comments_num'];
		$user_info['play_times']    = (int) $statistic['play_times'];
		$user_info['undress_times'] = (int) $statistic['undress_times'];
		
		if ( !empty($user) and $user->lvl() >= 15 ) $admin = true; else $admin = false;		
			
		ob_start(); include $this->GetView('custom_profile.html'); 
		
		return ob_get_clean();  	
	}

}
?>