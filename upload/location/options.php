<?php
if (!defined('MCR')) exit;
if (empty($user) or $user->lvl() <= 0) { header("Location: ".BASE_URL); exit; }

/* Default vars */
$page = lng('PAGE_OPTIONS');

$prefix = 'profile/';

$user_img_get = $user->getSkinLink().'&amp;refresh='.rand(1000, 9999);
$menu->SetItemActive('options');

if ($user->group() == 4 or !$user->email() or $user->gender() > 1) { // Not verificated EMail / Compatibility with older versions	
	
	loadTool('ajax.php'); $html_info = '';	
	
	if (CaptchaCheck(0, false)) {
	
		if (isset($_POST['female']) and $user->gender() > 1) 
		
			$user->changeGender((!(int)$_POST['female'])? 0 : 1);

		if (!empty($_POST['email'])) { 
		
			$send_result = $user->changeEmail($_POST['email'], true);
			
				if ( $send_result == 1) $html_info = lng('REG_CONFIRM_INFO');
			elseif ( $send_result == 1902) $html_info = lng('AUTH_EXIST_EMAIL');
			else $html_info = lng('MAIL_FAIL');				
		}
	} elseif ( isset($_POST['antibot']) ) $html_info = lng('CAPTCHA_FAIL');
	
	if ($user->group() == 4 or !$user->email() or $user->gender() > 1) {
	
	ob_start();	
	
	include View::Get('cp_form.html', $prefix);	
	
		if ( $user->group() == 4 or !$user->email() ) include View::Get('profile_email.html', $prefix);	
	
		if ($user->gender() > 1 ) include View::Get('profile_gender.html', $prefix);
			
	include View::Get('cp_form_footer.html', $prefix);
	
	$content_main .= ob_get_clean();
	}	
}

if ($user->group() != 4 ) {
	
	ob_start();	
	
	if ($user->getPermission('change_skin'))  include View::Get('profile_skin.html', $prefix);
	if ($user->getPermission('change_skin')   and !$user->defaultSkinTrigger()) 
											  include View::Get('profile_del_skin.html', $prefix); 
	if ($user->getPermission('change_cloak')) include View::Get('profile_cloak.html', $prefix);
	if ($user->getPermission('change_cloak')  and file_exists($user->getCloakFName())) 
											  include View::Get('profile_del_cloak.html', $prefix);  
	if ($user->getPermission('change_login')) include View::Get('profile_nik.html', $prefix);
	if ($user->getPermission('change_pass'))  include View::Get('profile_pass.html', $prefix);

	$profile_inputs = ob_get_clean();
	
	loadTool('profile.class.php'); $user_profile = new Profile($user, 'other/', 'profile', true);
	$profile_info = $user_profile->Show(false); 
	
	ob_start(); include View::Get('profile.html', $prefix);

	$content_main .= ob_get_clean();
} 	
?>