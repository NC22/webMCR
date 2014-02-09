<?php

require('../system.php');
$command = Filter::input('command');
$userlist = Filter::input('userlist');

if (!$command and !$userlist)
    exit('<script>parent.showResult("command is empty");</script>');

tokenTool('check');
$token = 'parent.token_data = "' . tokenTool('get') . '";';

DBinit('mcraft.rcon');

loadTool('rcon.class.php');
loadTool('user.class.php');

MCRAuth::userLoad();

if (empty($user) or $user->lvl() < 15)
    exit;

/* HTML version of GetUserList */

function GetUserListHTML($result)
{
    $str = trim($result);
    $str = str_replace(array("\r\n", "\n", "\r"), '', $str);

    $names = explode(', ', substr($str, 19));

    if (!empty($names))
        for ($i = 0; $i < sizeof($names); $i++)
            trim($names[$i]);

    if ($names[0] == '')
        unset($names);

    if (empty($names))
        return array('<p>Сервер пуст</p>', '');

    $html = '';
    $script = '';

    for ($i = 0; $i < sizeof($names); $i++) {

        $script .= 'parent.addNickButton("' . $names[$i] . '",' . $i . ');';
        $html .= '<p><a href="#" id="nickButton' . $i . '">' . $names[$i] . '</a></p>';
    }

    return array($html, $script);
}

/* Try load connect options */

$game_server = Filter::input('IP');
if (empty($game_server)) $game_server = sqlConfigGet('rcon-serv');

if ($game_server == 0)
    exit('<script>'.$token.'parent.showResult("rcon unconfigured");</script>');

$rcon_port = Filter::input('port', 'post', 'int');
if (empty($rcon_port)) $rcon_port =  (int) sqlConfigGet('rcon-port');

$rcon_pass = Filter::input('pass');
if (empty($rcon_pass)) $rcon_pass = sqlConfigGet('rcon-pass');

/* Sync or drop config */

if (Filter::input('save', 'post', 'bool')) {

    sqlConfigSet('rcon-serv', $game_server);
    sqlConfigSet('rcon-pass', $rcon_pass);
    sqlConfigSet('rcon-port', $rcon_port);
} else
    sqlConfigSet('rcon-serv', 0);

try {
    $rcon = new MinecraftRcon;
    $rcon->Connect($game_server, $rcon_port, $rcon_pass);

    if ($userlist) {

        $page = GetUserListHTML($rcon->Command('list'));
        exit("<script>'.$token.'parent.GetById('users_online').innerHTML = '" . $page[0] . "'; " . $page[1] . "</script>");
    }

    $command = str_replace(array("\r\n", "\n", "\r"), '', $command);
    $command = preg_replace('| +|', ' ', $command);

    $str = trim(TextBase::HTMLDestruct($rcon->Command($command)));
    $str = str_replace(array("\r\n", "\n", "\r"), '', $str);

    if (!strncmp($command, 'say', 3) and strlen($str) > 2)
        $str = substr($str, 2);
    if (!strncmp(substr($str, 2), 'Usage', 5))
        $str = substr($str, 2);

    $str = str_replace(array(chr(167)), '', $str);

    echo '<script>'.$token.'parent.showResult("' . $str . '");</script>';
    
} catch (MinecraftRconException $e) {
    echo '<script>'.$token.'parent.showResult("' . $e->getMessage() . '");</script>';
}

$rcon->Disconnect();
