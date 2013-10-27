<?php
if (!defined('MCR')) exit;

require(MCR_ROOT.'instruments/auth/usual.php');

Class MCMSAuth {

private static function getXFHashCheckLogic() {
	
		if (class_exists('XenForo_Authentication_Core12')) return 2 ; // ver 1.2.0
	elseif (class_exists('XenForo_Authentication_Core')) return 1 ; // ver 1.1.4 or lower
	
	return 0;	
}

/* Загрузка API главной CMS */

public static function start() {
global $site_ways;
 
if (class_exists('XenForo_Autoloader')) return;
 
if (empty($site_ways['main_cms'])) 
	exit('[MCMS] Не проинициализирован путь до дирректории Xenforo, проверьте опцию $site_ways[\'main_cms\'] в настройках скрипта авторизации.');
	
if (!file_exists($site_ways['main_cms']  . 'library/XenForo/Autoloader.php')) 
	exit('[MCMS] Файл "'.$site_ways['main_cms'].'library/XenForo/Autoloader.php" отсутствует. Путь до дирректории Xenforo указан не верно, проверьте опцию $site_ways[\'main_cms\'] в настройках скрипта авторизации.'); 

require($site_ways['main_cms']  . '/library/XenForo/Autoloader.php');

	XenForo_Autoloader::getInstance()->setupAutoloader($site_ways['main_cms'] . 'library'); 
	XenForo_Application::initialize($site_ways['main_cms'] . 'library', $site_ways['main_cms']); 
	XenForo_Application::set('page_start_time', microtime(true));
} 

/* Проверка авторизации пользователя в главной CMS */

public static function userLoad() {
global $site_ways;

	XenForo_Session::startPublicSession();
	$visitor = XenForo_Visitor::getInstance();

return $visitor->getUserId();
} 

/* Авторизация пользователя в главной CMS */

public static function login($id) {

	self::start();
	
	if (self::userLoad()) return;
	
	$loginModel = XenForo_Model::create('XenForo_Model_Login');
    $userModel = XenForo_Model::create('XenForo_Model_User');

	$userModel->setUserRememberCookie($id);

	XenForo_Model_Ip::log($id, 'user', $id, 'login');

    $userModel->deleteSessionActivity(0, GetRealIp());

	$session = XenForo_Application::get('session');
    $session->changeUserId($id);
    XenForo_Visitor::setup($id);
} 

/* Выход пользователя в главной CMS */

public static function logout() {
	
	self::start();
		
	if (!self::userLoad()) return;
	
	if (XenForo_Visitor::getInstance()->get('is_admin')) {

	$adminSession = new XenForo_Session(array('admin' => true));
	$adminSession->start();
				
	if ($adminSession->get('user_id') == XenForo_Visitor::getUserId()) 

	$adminSession->delete();

	}

	XenForo_Model::create('XenForo_Model_Session')->processLastActivityUpdateForLogOut(XenForo_Visitor::getUserId());

	XenForo_Application::get('session')->delete();
	XenForo_Helper_Cookie::deleteAllCookies(
	array('session'),
	array('user' => array('httpOnly' => false))
	);

	XenForo_Visitor::setup(0);
}

/* Проверка авторизации пользователя в webMCR */

public static function userInit() { 
global $user, $config;

	MCRAuth::LoadSession();
	
	if ($config['p_sync']) {
	
		self::start();
		
		$id = self::userLoad();

		if ($id) {
		
			 $user = new User($id);
			 
			 if ($user->lvl() <= 0)
			 
			 $user = false; 
			 
			 else 
			 
			 $user->login(randString(15),GetRealIp());	
			 
		} elseif (!empty($user)) {
		
			$user->logout();
			$user = false;
		}
	}
}

/* Генерация пароля */

public static function createPass($password) { 	
	return 0;
}

/* 
	Проверка пароля при авторизации 
	data [4]
	'pass_db'	 - зашифрованный пароль из БД, 
	'pass'		 - введенный пароль
	'user_id'	 - идентификатор пользователя
	'user_name'	 - логин пользователя
*/

public static function checkPass($data) { 
global $bd_names, $bd_users;
	
	self::start();
	
	$db  = XenForo_Application::get('db');
	
	$XFauthLogic = self::getXFHashCheckLogic(); 
	if ($XFauthLogic == 2) 
	
		$auth = new XenForo_Authentication_Core12;
	
	elseif ($XFauthLogic == 1)
	
		$auth = new XenForo_Authentication_Core;
	
	else {
	
		vtxtlog ('[xenforo.php] xenForo auth class not founded');
		return false;		
	}
	
	$res     = $db->fetchCol("SELECT `data` FROM `{$bd_names['user_auth']}` WHERE `{$bd_users['id']}`=" . $data['user_id']);
			
	if (!count($res)) return false;

	$auth->setData($res[0]);
		
	if ( $auth->authenticate($data['user_id'], $data['pass']) ) 
	
	return true;
	
	else return false;	
}

}