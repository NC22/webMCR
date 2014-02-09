<?php
require('./system.php');

$login = Filter::input('login');
$out = Filter::input('out', 'get', 'bool');

if (!$out and !$login)
    exit;

loadTool('ajax.php');
loadTool('user.class.php');

DBinit('login');

if ($out) {

    header("Location: " . BASE_URL);
    MCRAuth::userLoad();
    if (!empty($user))
        $user->logout();
} elseif ($login) {
    
    $pass = Filter::input('pass');
    $tmp_user = new User($login, (strpos($login, '@') === false) ? $bd_users['login'] : $bd_users['email']);
    $ajax_message['auth_fail_num'] = (int) $tmp_user->auth_fail_num();

    if (!$tmp_user->id())
        aExit(4, lng('AUTH_NOT_EXIST'));

    if ($tmp_user->auth_fail_num() >= 5)
        CaptchaCheck(6);

    if (!$tmp_user->authenticate($pass)) {

        $ajax_message['auth_fail_num'] = (int) $tmp_user->auth_fail_num();
        aExit(1, lng('AUTH_FAIL') . '.<br /> <a href="#" style="color: #656565;" onclick="RestoreStart(); return false;">' . lng('AUTH_RESTORE') . ' ?</a>');
    }

    if ($tmp_user->lvl() <= 0)
        aExit(4, lng('USER_BANNED'));

    $tmp_user->login(randString(15), GetRealIp(), Filter::input('save', 'post', 'bool'));
    aExit(0, 'success');
}
