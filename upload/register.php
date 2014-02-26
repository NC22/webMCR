<?php

require('./system.php');

function CheckPostComplect()
{
    $input = array(
        'login' => Filter::input('login'),
        'pass' => Filter::input('pass'),
        'repass' => Filter::input('repass'),
        'email' => Filter::input('email', 'post', 'mail'),
        'female' => Filter::input('female', 'post', 'bool'),
        'verificate' => Filter::input('verificate', 'get'),
        'id' => Filter::input('id', 'post', 'int'),
        'method' => false
    );

    if (!$input['id']) $input['id'] = Filter::input('id', 'get', 'int');
    
    if ($input['login'] and
        $input['pass'] and
        $input['repass'])
        $input['method'] = 1;
        
    if ($input['verificate'] and
        $input['id'])
        $input['method'] = 2;

    return $input;
}

$input = CheckPostComplect();
if (!$input['method'])
    exit;

loadTool('ajax.php');

if ($config['p_logic'] != 'usual' and $config['p_logic'] != 'xauth' and $config['p_logic'] != 'authme')
    aExit(1, 'Registration is blocked. Used auth script from main CMS');

DBinit('register');

loadTool('user.class.php');
$rcodes = array();

function tryExit()
{
    global $rcodes;

    $message = '';
    $rnum = sizeof($rcodes);
    if (!$rnum)
        return;

    for ($i = 0; $i < $rnum; $i++) {

        $modifed = true;

        switch ($rcodes[$i]) {
            case 2 : $message .= lng('INCORRECT') . '. (' . lng('LOGIN') . ')';
                break;
            case 3 : $message .= lng('INCORRECT') . '. (' . lng('PASS') . ')';
                break;
            case 4 : $message .= lng('INCORRECT') . '. (' . lng('REPASS') . ')';
                break;
            case 12 : $message .= lng('INCORRECT') . '. (' . lng('EMAIL') . ')';
                break;
            case 6 : $message .= lng('INCORRECT_LEN') . '. (' . lng('LOGIN') . ')';
                break;
            case 7 : $message .= lng('INCORRECT_LEN') . '. (' . lng('PASS') . ')';
                break;
            case 8 : $message .= lng('INCORRECT_LEN') . '. (' . lng('REPASS') . ')';
                break;
            case 9 : $message .= lng('REPASSVSPASS');
                break;
            case 13 : $message .= lng('INCORRECT_LEN') . '. (' . lng('EMAIL') . ')';
                break;
            default : $modifed = false;
                break;
        }

        if ($modifed)
            $message .= "<br />";
    }

    aExit(2, $message);
}

if ($input['method'] == 2) {

    $tmp_user = new User($input['id']);

    if ($tmp_user->id() and !strcmp($tmp_user->getVerificationStr(), $input['verificate']))
        $tmp_user->changeGroup(1);

    exit(View::ShowStaticPage('mail_verification_ok.html', 'other/'));
}

RefreshBans();
$female = ($input['female']) ? 1 : 0;

if (!CanAccess())
    aExit(11, lng('IP_BANNED'));

if (empty($input['login']) || empty($input['pass']) || empty($input['repass']))
    aExit(1, lng('INCOMPLETE_FORM'));

if (!preg_match("/^[a-zA-Z0-9_-]+$/", $input['login']))
    $rcodes[] = 2;
if (!preg_match("/^[a-zA-Z0-9_-]+$/", $input['pass']))
    $rcodes[] = 3;
if (!preg_match("/^[a-zA-Z0-9_-]+$/", $input['repass']))
    $rcodes[] = 4;
if (!$input['email'])
    $rcodes[] = 12;

tryExit();

$sql = "SELECT COUNT(*) FROM `{$bd_names['users']}` "
        . "WHERE `{$bd_users['login']}`=:login";

$line = getDB()->fetchRow($sql, array('login' => $input['login']), 'num');

if ($line[0])
    aExit(5, lng('AUTH_EXIST_LOGIN'));

$sql = "SELECT COUNT(*) FROM `{$bd_names['users']}` "
        . "WHERE `{$bd_users['email']}`=:email";

$line = getDB()->fetchRow($sql, array('email' => $input['email']), 'num');

if ($line[0])
    aExit(15, lng('AUTH_EXIST_EMAIL'));

if ((strlen($input['login']) < 4) or (strlen($input['login']) > 15))
    $rcodes[] = 6;
if ((strlen($input['pass']) < 4) or (strlen($input['pass']) > 15))
    $rcodes[] = 7;
if ((strlen($input['repass']) < 4) or (strlen($input['repass']) > 15))
    $rcodes[] = 8;
if (strlen($input['email']) > 50)
    $rcodes[] = 13;
if (strcmp($input['pass'], $input['repass']))
    $rcodes[] = 9;

tryExit();

$verification = (bool) sqlConfigGet('email-verification');

if ($verification)
    $group = 4;
else
    $group = 1;

$sql = "INSERT INTO `{$bd_names['users']}` ("
        . "`{$bd_users['login']}`,"
        . "`{$bd_users['password']}`,"
        . "`{$bd_users['ip']}`,"
        . "`{$bd_users['female']}`,"
        . "`{$bd_users['ctime']}`,"
        . "`{$bd_users['group']}`) VALUES(:login, :pass, :ip, '$female', NOW(),'$group')";

$result = getDB()->ask($sql, array(
    'login' => $input['login'],
    'pass' => MCRAuth::createPass($input['pass']),
    'ip' => GetRealIp()
        ));

if (!$result)
    aExit(14);

$tmp_user = new User(getDB()->lastInsertId());
$tmp_user->setDefaultSkin();

$next_reg = (int) sqlConfigGet('next-reg-time');

if ($next_reg > 0)
    getDB()->ask("INSERT INTO `{$bd_names['ip_banning']}` (`IP`,`time_start`,`ban_until`) "
            . "VALUES (:ip, NOW(), NOW()+INTERVAL $next_reg HOUR)", array('ip' => $_SERVER['REMOTE_ADDR']));

    
if ($tmp_user->changeEmail($input['email'], $verification) > 1)
    aExit(14, lng('MAIL_FAIL'));

if (!$verification)
    aExit(0, lng('REG_COMPLETE') . '. <a href="#" class="btn" onclick="Login();">' . lng('ENTER') . '</a>');
else 
    aExit(0, lng('REG_COMPLETE') . '. ' . lng('REG_CONFIRM_INFO'));
