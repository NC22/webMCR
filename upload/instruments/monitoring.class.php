<?php

if (!defined('MCR'))
    exit;

class Server extends Item
{
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

    public function Server($id = false, $style_sd = false)
    {
        global $bd_names;

        parent::__construct($id, ItemType::Server, $bd_names['servers'], $style_sd);

        if (!$this->id)
            return false;

        $line = getDB()->fetchRow("SELECT * FROM `" . $this->db . "` WHERE id='{$this->id}'");
        if (!$line) {
            $this->id = false;
            return false;
        }

        $this->address = $line['address'];
        $this->port = (int) $line['port'];
        $this->method = (int) $line['method'];

        $this->name = $line['name'];
        $this->slots = (int) $line['slots'];
        $this->info = $line['info'];
        $this->numpl = (int) $line['numpl'];
        $this->online = ($line['online']) ? true : false;

        $this->refresh = (int) $line['refresh_time'];
        $this->rcon = (!strlen($line['rcon'])) ? false : $line['rcon'];

        $this->s_user = (!strlen($line['service_user'])) ? false : $line['service_user'];

        return true;
    }

    public function Create($address, $port, $method = 0, $rcon = false, $name = '', $info = '', $s_user = false)
    {
        if ($this->Exist())
            return 0;

        if (!$address or !TextBase::StringLen($address))
            return false;

        $method = (int) $method;
        if ($method < 0 or $method > 3)
            $method = 0;

        if ($rcon and !($method == 2 or $method == 3))
            $rcon = '';

        if (!$rcon and ( $method == 2 or $method == 3))
            return 2;
        if (!$s_user and $method == 3)
            return 3;

        $port = (int) $port;
        if (!$port)
            $port = 25565;

        $sql = "INSERT INTO `" . $this->db . "` ( address, port, info, name, method, service_user, rcon ) "
                . "VALUES (:address, :port, :info, :name, :method, :suser, :rcon)";

        $result = getDB()->ask($sql, array(
            'address' => $address,
            'port' => $port,
            'info' => $info,
            'name' => $name,
            'method' => $method,
            'suser' => $s_user,
            'rcon' => $rcon
        ));

        if ($result)
            $this->id = getDB()->lastInsertId();
        else
            return 4;

        $this->address = $address;
        $this->port = $port;
        $this->method = $method;
        $this->info = $info;
        $this->name = $name;
        $this->rcon = $rcon;
        $this->s_user = $s_user;

        return 1;
    }

    public function SetConnectMethod($method = 0, $rcon = '', $s_user = '')
    {
        if (!$this->Exist())
            return false;

        $method = (int) $method;
        if ($method < 0 or $method > 3)
            $method = 0;

        if ($rcon and !( $method == 2 or $method == 3))
            $rcon = '';
        if (!$rcon and ( $method == 2 or $method == 3))
            return false;
        if (!$s_user and $method == 3)
            return false;

        $sql = "UPDATE `" . $this->db . "` SET "
                . "`method`=:method,"
                . "`rcon`=:rcon,"
                . "`service_user`=:suser WHERE `id`='{$this->id}'";

        getDB()->ask($sql, array(
            'method' => $method,
            'suser' => $s_user,
            'rcon' => $rcon
        ));

        $this->method = $method;
        $this->rcon = $rcon;
        $this->s_user = $s_user;
    }

    public function SetConnectWay($address, $port)
    {
        if (!$this->Exist())
            return false;

        if (!$address or !TextBase::StringLen($address))
            return false;

        $port = (int) $port;
        if (!$port)
            $port = 25565;

        $sql = "UPDATE `" . $this->db . "` SET "
                . "`address`=:address,"
                . "`port`=:port WHERE `id`='{$this->id}'";

        getDB()->ask($sql, array(
            'address' => $address,
            'port' => $port
        ));

        $this->address = $address;
        $this->port = $port;
        return true;
    }

    public function SetText($var, $field = 'name')
    {
        if (!$this->Exist())
            return false;
        else if ($field !== 'name' and $field !== 'info')
            return false;

        if (!$var or !TextBase::StringLen($var))
            return false;

        getDB()->ask("UPDATE `" . $this->db . "` SET `$field`=:var WHERE `id`='" . $this->id . "'", array('var' => $var));

        if ($field == 'name')
            $this->name = $var;
        else
            $this->info = $var;
    }

    private function IsTimeToUpdate()
    {
        if (!$this->Exist())
            return false;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$this->db}` "
                . "WHERE id='{$this->id}' "
                . "AND `last_update`<NOW()-INTERVAL {$this->refresh} MINUTE", false, 'num');

        if ((int) $line[0])
            return true;
        else
            return false;
    }

    public function UpdateState($extra = false)
    {
        global $config;

        if ((!$extra and !$this->IsTimeToUpdate()) or !$this->Exist())
            return;

        $this->online = false;
        $users_list = NULL;

        if (empty($this->address)) {
            getDB()->ask("UPDATE `" . $this->db . "` SET `online`='0',`last_update`=NOW() WHERE `id`='" . $this->id . "'");
            return;
        }

        getDB()->ask("UPDATE `" . $this->db . "` SET `last_update`=NOW() WHERE `id`='" . $this->id . "'");
        switch ($this->method) 
        {
            case 2: // RCON Connect 

                loadTool('rcon.class.php');

                try {

                    $rcon = new MinecraftRcon;
                    $rcon->Connect($this->address, $this->port, $this->rcon);
                    $str = $rcon->Command('list');
                } catch (MinecraftRconException $e) {

                    if ($e->getMessage() == 'Server offline') {
                        getDB()->ask("UPDATE `" . $this->db . "` SET `online`='0' WHERE `id`='" . $this->id . "'");
                        return;
                    }
                }

                $str = str_replace(array("\r\n", "\n", "\r"), '', $str);
                $names = explode(', ', substr($str, 19));

                if (!empty($names))
                    for ($i = 0; $i < sizeof($names); $i++)
                        trim($names[$i]);
                if (!$names[0] == '')
                    $users_list = $names;

                break;

            case 3: // json-api

                loadTool('json_api.php', 'bukkit/');

                $salt = sqlConfigGet('json-verification-salt');

                if (!$salt) {

                    $salt = md5(rand(1000000000, 2147483647) . rand(1000000000, 2147483647));
                    sqlConfigSet('json-verification-salt', $salt);
                }

                if (!extension_loaded("cURL")) {
                    vtxtlog('[monitoring.class.php] cURL module is required');
                    return;
                }

                $api = new JSONAPI($this->address, $this->port, $this->s_user, $this->rcon, $salt); // ToDo rewrite / delete . curl is custom module

                $apiresult = $api->call(array("getPlayerLimit", "getPlayerCount"), array(NULL, NULL));

                if (!$apiresult) {

                    getDB()->ask("UPDATE `" . $this->db . "` SET `online`='0' WHERE `id`='" . $this->id . "'");
                    return;
                }

                $full_state = array(
                    'numpl' => $apiresult["success"][1]["success"], 
                    'maxplayers' => $apiresult["success"][0]["success"]
                );
                
                break;         	

            default : // query, simple query

                loadTool('query.function.php');

                $full_state = ($this->method == 1) ? mcraftQuery($this->address, $this->port) : mcraftQuery_SE($this->address, $this->port);
                
                if (empty($full_state) or isset($full_state['too_many'])) {

                    getDB()->ask("UPDATE `" . $this->db . "` "
                            . "SET `online`='" . ((isset($full_state['too_many'])) ? '1' : '0') . "' "
                            . "WHERE `id`='" . $this->id . "'");

                    $this->online = (isset($full_state['too_many'])) ? true : false;
                    return;
                } elseif (!empty($full_state['players'])) {

                    $users_list = $full_state['players'];
                }

                break;
        }

        $this->online = true;

        $system_users = '';
        $numpl = (!empty($full_state['numpl'])) ? $full_state['numpl'] : 0;

        if ($users_list) {

            $numpl = sizeof($users_list);

            if ($numpl == 1){
                $system_users = $users_list[0];
            } else {
                for ($i = 0; $i < $numpl; $i++) {

                    $system_users .= ($i == 0) ? $users_list[$i] : ',' . $users_list[$i];
                }
            }
        }

        $this->slots = (!empty($full_state)) ? $full_state['maxplayers'] : -1;
        $this->numpl = $numpl;

        if (!empty($full_state)) { 
            
            $sql = "UPDATE `" . $this->db . "` SET `numpl`=:numpl, slots=:slots, players=:players, online='1' "
                 . "WHERE `id`='" . $this->id . "'";
            
         getDB()->ask($sql, array(
             'numpl' => $this->numpl, 
             'slots' =>  $this->slots, 
             'players' => $system_users
         ));
         
        } else {
            
            $sql = "UPDATE `" . $this->db . "` SET `numpl`=:numpl, slots='-1', players=:players, online='1' "
                 . "WHERE `id`='{$this->id}'";
                 
             getDB()->ask($sql, array('numpl' => $this->numpl, 'players' => $system_users));                
                 
        }
    }

    public function GetPlayers()
    {
        if (!$this->Exist())
            return false;

        $players = getDB()->fetchRow("SELECT `players`, `numpl` FROM `" . $this->db . "` WHERE `id`='" . $this->id . "'");

        $list = $players['players'];
        $numpl = (int) $players['numpl'];

        if (!strlen($list) and !$numpl)
            return array("Сервер пуст", 0);

        if (!sizeof(explode(',', $list)) and !$numpl)
            return array("Сервер пуст", 0);
        else
            return array($list, $numpl);
    }

    public function SetVisible($page, $state)
    {
        if (!$this->Exist())
            return false;

        $page = ServerManager::getPageName($page);
        if (!$page)
            return false;

        $state = ($state) ? 1 : 0;

        getDB()->ask("UPDATE `" . $this->db . "` SET `$page`='$state' WHERE `id`='" . $this->id . "'");
    }

    public function GetVisible($param)
    {
        if (!$this->Exist())
            return -1;

        $param = ServerManager::getPageName($param);
        if (!$param)
            return false;

        $line = getDB()->fetchRow("SELECT `$param` FROM `" . $this->db . "` WHERE `id`='" . $this->id . "'", false, 'num');

        if ($line) {
            return ((int) $line[0]) ? true : false;
        } else
            return -1;
    }

    public function SetRefreshTime($newTimeout)
    {
        if (!$this->Exist())
            return false;

        $newTimeout = (int) $newTimeout;
        if ($newTimeout < 0)
            $newTimeout = 0;

        getDB()->ask("UPDATE `" . $this->db . "` SET `refresh_time`=:timeout WHERE `id`='" . $this->id . "'", array('timeout' => $newTimeout));

        $this->refresh = $newTimeout;
        return true;
    }

    public function SetPriority($new)
    {
        if (!$this->Exist())
            return false;

        $new = (int) $new;
        if ($new < 0)
            $new = 0;

        getDB()->ask("UPDATE `" . $this->db . "` SET `priority`=:priority WHERE `id`='" . $this->id . "'", array('priority' => $new));

        return true;
    }

    public function GetPriority()
    {
        if (!$this->Exist())
            return false;

        $line = getDB()->fetchRow("SELECT `priority` FROM `" . $this->db . "` WHERE `id`='" . $this->id . "'", false, 'num');

        if ($line) {
            return (int) $line[0];
        } else
            return false;
    }

    public function ShowHolder($type = 'side', $server_prefix = '')
    {
        if (!ServerManager::getPageName($type))
            return false;

        ob_start();

        $server_name = $this->name;
        $server_info = $this->info;  // this->address - фактический адресс
        $server_id = $this->id;
        $server_pid = $server_prefix . $server_id;
        $server_numpl = $this->numpl;
        $server_slots = $this->slots;

        if ((int) $this->slots != -1)
            $server_pl_inf = $this->numpl . '/' . $this->slots;
        else
            $server_pl_inf = $this->numpl;

        switch ($type) {
            case 'mon':
            case 'side': include $this->GetView('serverstate_' . $type . '.html');
                break;
            case 'game':

                if ($this->online)
                    include $this->GetView('serverstate_' . $type . '_online.html');
                else
                    include $this->GetView('serverstate_' . $type . '_offline.html');

                break;
            default: return false;
                break;
        }

        return ob_get_clean();
    }

    public function ShowInfo()
    {
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

        if (!$this->id)
            aExit(1, 'server_state');

        $ajax_message['id'] = (int) $this->id;
        $ajax_message['name'] = $this->name;
        $ajax_message['online'] = ($this->online) ? 1 : 0;
        $ajax_message['info'] = $this->info;
        $ajax_message['address'] = $this->address;
        $ajax_message['port'] = (int) $this->port;

        if (!$this->online)
            aExit(2, 'server_state');

        $players = $this->GetPlayers();

        $ajax_message['numpl'] = (int) $players[1];
        $ajax_message['slots'] = (int) $this->slots;
        $ajax_message['pl_array'] = $players[0];

        aExit(0, 'server_state');
    }

    public function getInfo()
    {
        if (!$this->Exist())
            return false;

        return array(
            'id' => $this->id,
            'address' => $this->address,
            'online' => $this->online,
            'refresh' => $this->refresh,
            'port' => $this->port,
            's_user' => $this->s_user,
            'name' => $this->name,
            'method' => $this->method,
            'info' => $this->info
         );
    }

    public function info()
    {
        return $this->info;
    }

    public function online()
    {
        return ($this->online) ? true : false;
    }

    public function name()
    {
        return $this->name;
    }

}

Class ServerManager extends View
{

    public function __construct($style_sd = false)
    {
        global $site_ways;

        parent::View($style_sd);
    }

    public function Show($type = 'side', $update = false)
    {
        global $bd_names;

        $page = self::getPageName($type);
        if (!$page)
            return false;

        $html_serv = $this->ShowPage('serverstate_' . $type . '_header.html');

        $result = getDB()->ask("SELECT `id` FROM `{$bd_names['servers']}` WHERE `$page`=1 ORDER BY `priority` DESC LIMIT 0,10", false);

        while ($line = $result->fetch('num')) {

            $found = true;

            $server = new Server($line[0], $this->st_subdir);
            if ($update)
                $server->UpdateState();
            $html_serv .= $server->ShowHolder($type);

            unset($server);
        }

        if (!isset($found)) $html_serv .= $this->ShowPage('serverstate_' . $type . '_empty.html');

        $html_serv .= $this->ShowPage('serverstate_' . $type . '_footer.html');

        return $html_serv;
    }

    public static function getPageName($page)
    {
        switch ($page) {
            case 'side': return 'main_page';
                break;
            case 'game': return 'news_page';
                break;
            case 'mon': return 'stat_page';
                break;
            default: return false;
                break;
        }
    }

}

?>
