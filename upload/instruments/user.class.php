<?php
if (!defined('MCR')) exit;

/* User class | User group class */

Class User {
    
    private $db;
    private $id = false;
    private $permissions = null;
    private $tmp;
    private $ip;
    private $name;
    private $email;
    private $lvl;
    private $group;
    private $gender;
    private $female;
    private $deadtry;

    /** @const */
    public static $date_statistic = array(
        'create_time',
        'gameplay_last',
        'active_last',
    );

    /** @const */
    public static $int_statistic = array(
        'comments_num',
        'play_times',
        'undress_times',
    );

    public function __construct($input, $method = false)
    {
        global $bd_users, $bd_names;

        $this->db = $bd_names['users'];
        
        if (!$method) {
            $method = $bd_users['id'];
        }
        
        $method = $bd_users[array_search($method, $bd_users)];

        if ($method === $bd_users['id']) {
            $input = (int) $input;
        }

        if (!$input) {
            return false;
        }

        $sql = "SELECT  `{$bd_users['login']}`,
                        `{$bd_users['id']}`,
                        `{$bd_users['tmp']}`,
                        `{$bd_users['ip']}`,
                        `{$bd_users['email']}`,
                        `{$bd_users['deadtry']}`,
                        `{$bd_users['female']}`,
                        `{$bd_users['group']}` FROM `{$this->db}` "
                . "WHERE `$method`=:input";

        $line = getDB()->fetchRow($sql, array('input' => $input));
        if (!$line) {
            return false;
        }

        $this->permissions = null;

        $this->id = (int) $line[$bd_users['id']];
        $this->name = $line[$bd_users['login']];
        $this->group = (int) $line[$bd_users['group']];
        $this->lvl = $this->getPermission('lvl');

        $this->tmp = $line[$bd_users['tmp']];
        $this->ip = $line[$bd_users['ip']];

        $this->email = $line[$bd_users['email']];
        $this->deadtry = (int) $line[$bd_users['deadtry']];

        /* Пол персонажа */
        $gender = $line[$bd_users['female']];

        $this->gender = (is_numeric($gender)) ? (int) $gender : (($gender == 'female' or $gender == 'male') ? (($gender == 'female') ? 1 : 0) : 10);
        $this->female = ($this->gender == 1) ? true : false;

        return true;
    }

    public function activity()
    {
        global $bd_users;

        if ($this->id) {
            getDB()->ask("UPDATE `{$this->db}` SET `active_last`= NOW() "
                    . "WHERE `{$bd_users['id']}`='" . $this->id . "'");
        }
    }

    public function isOnline()
    {
        if ($this->tmp === '0')
            return false;

        $last_active = $this->getStatisticTime('active_last');
        if (!$last_active)
            return false;

        if (time() - strtotime($last) > 300)
            return false;

        return true;
    }

    public function authenticate($pass)
    {
        global $bd_users;

        if (!$this->id)
            return false;

        $sql = "SELECT `{$bd_users['password']}` FROM `{$this->db}` "
                . "WHERE `{$bd_users['id']}`='" . $this->id . "'";
        $line = getDB()->fetchRow($sql, false, 'num');

        $auth_info = array('pass_db' => $line[0],
            'pass' => $pass,
            'user_id' => $this->id,
            'user_name' => $this->name
        );

        $test_pass = MCRAuth::checkPass($auth_info);

        if (!$test_pass) {

            getDB()->ask("UPDATE `{$this->db}` SET `{$bd_users['deadtry']}`= {$bd_users['deadtry']} + 1 "
                    . "WHERE `{$bd_users['id']}`='" . $this->id . "'");

            $this->deadtry++;
        }

        return ($test_pass) ? true : false;
    }

    public function login($tmp, $ip, $save = false)
    {
        global $bd_users, $config;

        if (!$this->id)
            return false;

        $save = ($save) ? true : false;

        if ($config['p_logic'] != 'usual' and $config['p_sync'])
            MCMSAuth::login($this->id());

        $sql = "UPDATE `{$this->db}` SET `{$bd_users['deadtry']}` = '0', "
                . "`{$bd_users['tmp']}`=:tmp, "
                . "`{$bd_users['ip']}`=:ip "
                . "WHERE `{$bd_users['id']}`='" . $this->id . "'";

        getDB()->ask($sql, array('ip' => $ip, 'tmp' => $tmp));

        $this->tmp = $tmp;

        if (!isset($_SESSION))
            session_start();

        $_SESSION['user_id'] = $this->id();
        $_SESSION['user_name'] = $this->name();
        $_SESSION['ip'] = $this->ip();

        if ($save)
            setcookie("PRTCookie1", $tmp, time() + 60 * 60 * 24 * 30 * 12, '/');

        return true;
    }

    public function logout()
    {
        global $bd_users, $config;

        if ($config['p_logic'] != 'usual' and $config['p_sync'])
            MCMSAuth::logout();

        if (!isset($_SESSION))
            session_start();
        if (isset($_SESSION))
            session_destroy();

        $this->tmp = 0;

        $sql = "UPDATE `{$this->db}` SET `{$bd_users['tmp']}`='0' "
                . "WHERE `{$bd_users['id']}`='" . $this->id . "'";

        getDB()->ask($sql);

        if (isset($_COOKIE['PRTCookie1']))
            setcookie("PRTCookie1", "", time() - 3600);
    }

    public function canPostComment()
    {
        global $bd_names;

        if (!$this->getPermission('add_comm'))
            return false;

        if ($this->group() == 3)
            return true;

        /* Интервал по времени 1 минута */

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$bd_names['comments']}` "
                . "WHERE `user_id`='" . $this->id . "' "
                . "AND `time`>NOW()-INTERVAL 1 MINUTE", false, 'num');

        if ((int) $line[0])
            return false;

        return true;
    }

    public function gameLoginConfirm()
    {
        global $bd_users;

        if (!$this->id)
            return false;

        getDB()->ask("UPDATE `{$this->db}` SET "
                . "`gameplay_last`=NOW(),"
                . "`play_times`=`play_times`+1 WHERE `{$bd_users['id']}`='" . $this->id . "'");

        return true;
    }

    public function gameLogoutConfirm()
    {
        global $bd_users;

        if (!$this->id)
            return false;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$this->db}` "
                . "WHERE `{$bd_users['server']}` IS NOT NULL "
                . "AND `{$bd_users['id']}`='" . $this->id . "'", false, 'num');

        if ((int) $line[0])
            getDB()->ask("UPDATE `{$this->db}` SET `{$bd_users['server']}`=NULL "
                    . "WHERE `{$bd_users['id']}`='" . $this->id . "'");

        return true;
    }

    public function gameLoginLast()
    {
        global $bd_users;

        if (!$this->id)
            return false;

        $line = getDB()->fetchRow("SELECT `gameplay_last` FROM `{$this->db}` "
                . "WHERE `gameplay_last` <> '0000-00-00 00:00:00' "
                . "AND `{$bd_users['id']}`='" . $this->id . "'");

        if ($line)
            return $line['gameplay_last'];
        else
            return false;
    }

    public function getStatisticTime($param)
    {
        global $bd_users, $config;

        if (!$this->id)
            return false;
        
        $param = self::$date_statistic[array_search($param, self::$date_statistic)];
 
        if ($param === 'create_time')
            $param = $bd_users['ctime'];

        $line = getDB()->fetchRow("SELECT `$param` FROM `{$this->db}` "
                . "WHERE `$param`<>'0000-00-00 00:00:00' "
                . "AND `{$bd_users['id']}`='" . $this->id . "'");

        if ($line) {

            if ($config['p_logic'] == 'xenforo' or
                    $config['p_logic'] == 'ipb' or
                    $config['p_logic'] == 'dle')
                return date('Y-m-d H:i:s', (int) $line[$param]); // from UNIX time		

            return $line[$param];
        } else
            return false;
    }

    public function getStatistic()
    {
        global $bd_users;

        if (!$this->id)
            return false;

        $line = getDB()->fetchRow("SELECT `" . implode("`, `", self::$int_statistic) . "` "
                . "FROM `{$this->db}` WHERE `{$bd_users['id']}`='" . $this->id . "'");

        if ($line)
            return $line;
        else
            return false;
    }

    public function setStatistic($field_name, $var)
    {
        global $bd_users;

        if (!$this->id)
            return false;

        $field_name = self::$int_statistic[array_search($field_name, self::$int_statistic)];

        $var = (int) $var;
        $dec = ( $var < 0 ) ? '-' : '+';
        $var = abs($var);

        if ($var > 0)
            $sql_var = '`' . $field_name . '`' . $dec . $var;
        else
            $sql_var = "'0'";

        getDB()->ask("UPDATE `{$this->db}` SET `" . $field_name . "`=" . $sql_var . " "
                . "WHERE {$bd_users['id']}='" . $this->id . "'");

        return true;
    }

    public function getMoney()
    {
        global $bd_names, $bd_money;

        if (!$this->id)
            return 0;

        if ($bd_names['iconomy']) {


            $line = getDB()->fetchRow("SELECT `{$bd_money['money']}` FROM `{$bd_names['iconomy']}` "
                    . "WHERE `{$bd_money['login']}`=:login", array('login' => $this->name));


            if (!$line) {

                getDB()->ask("INSERT INTO `{$bd_names['iconomy']}` (`{$bd_money['login']}`) "
                        . "VALUES (:login)", array('login' => $this->name));
                return 0;
            }

            return floatval($line[0]);
        }

        return 0;
    }

    public function addMoney($num)
    {
        global $bd_names, $bd_money;

        if (!$this->id)
            return false;
        if (!$bd_names['iconomy'])
            return false;

        $num = (int) $num;
        if (!$num)
            return $this->getMoney();

        $new_pl_money = $this->getMoney() + $num;
        if ($new_pl_money < 0)
            $new_pl_money = 0;

        getDB()->ask("UPDATE `{$bd_names['iconomy']}` SET `{$bd_money['money']}`=:money "
                . "WHERE `{$bd_money['login']}`=:login", array('money' => $new_pl_money, 'login' => $this->name));

        return $new_pl_money;
    }

    public function getSkinFName()
    {
        global $site_ways;
        return MCRAFT . $site_ways['skins'] . $this->name . '.png';
    }

    public function getCloakFName()
    {
        global $site_ways;
        return MCRAFT . $site_ways['cloaks'] . $this->name . '.png';
    }

    public function getGroupName()
    {
        global $bd_names;
        if (!$this->id)
            return false;

        $line = getDB()->fetchRow("SELECT `name` FROM `{$bd_names['groups']}` WHERE `id`='{$this->group}'", false, 'num');

        if (!$line)
            return 'unnamed';

        return $line[0];
    }

    public function deleteSkin()
    {
        if (file_exists($this->getSkinFName())) {
            unlink($this->getSkinFName());
            $this->deleteBuffer();
        }
    }

    public function deleteCloak()
    {
        if (file_exists($this->getCloakFName())) {
            unlink($this->getCloakFName());
            $this->deleteBuffer();
        }
    }

    public function defaultSkinMD5()
    {

        if (!$this->id)
            return false;

        $def_dir = MCRAFT . 'tmp/default_skins/';

        if ($this->isFemale())
            $default_skin_md5 = $def_dir . 'md5_female.md5';
        else
            $default_skin_md5 = $def_dir . 'md5.md5';

        if (file_exists($default_skin_md5)) {

            $md5 = @file($default_skin_md5);
            if ($md5[0])
                return $md5[0];
            else {
                vtxtlog('[action.php] error while READING md5 cache file. ' . $default_skin_md5);
                return false;
            }
        }

        if ($this->isFemale())
            $default_skin = $def_dir . 'Char_female.png';
        else
            $default_skin = $def_dir . 'Char.png';

        if (file_exists($default_skin)) {

            $md5 = md5_file($default_skin);
            if (!$md5) {
                vtxtlog('[action.php] md5 generate error. ' . $default_skin);
                return false;
            }

            if ($fp = fopen($default_skin_md5, 'w')) {
                if (!fwrite($fp, $md5))
                    vtxtlog('[action.php] error while SAVE cache file. ' . $default_skin_md5);
                fclose($fp);
            } else
                vtxtlog('[action.php] error while CREATE cache file. ' . $default_skin_md5);

            return $md5;
        } else {
            vtxtlog('[action.php] default skin file missing. ' . $default_skin);
            return false;
        }
    }
    
    /* is player use unique skin */
    public function defaultSkinTrigger($new_value = -1)
    { 
        global $bd_users;

        if (!$this->id)
            return false;

        if ($new_value < 0) {

            $line = getDB()->fetchRow("SELECT `default_skin` FROM `{$this->db}` "
                    . "WHERE `{$bd_users['id']}`='{$this->id()}'", false, 'num');

            $trigger = (int) $line[0];

            if ($trigger == 2) {

                if (!file_exists($this->getSkinFName()))
                    $trigger = 1;
                elseif (!strcmp($this->defaultSkinMD5(), md5_file($this->getSkinFName())))
                    $trigger = 1;
                else
                    $trigger = 0;

                getDB()->ask("UPDATE `{$this->db}` SET `default_skin`='$trigger' "
                        . "WHERE `{$bd_users['id']}`='{$this->id()}'");
            }
            return ($trigger) ? true : false;
        }

        $new_value = ($new_value) ? 1 : 0;

        getDB()->ask("UPDATE `{$this->db}` SET `default_skin`='$new_value' WHERE `{$bd_users['id']}`='{$this->id()}'");

        return ($new_value) ? true : false;
    }

    public function deleteBuffer()
    {

        $mini = MCRAFT . 'tmp/skin_buffer/' . $this->name . '_Mini.png';
        $skin = MCRAFT . 'tmp/skin_buffer/' . $this->name . '.png';

        if (file_exists($mini))
            unlink($mini);
        if (file_exists($skin))
            unlink($skin);
    }

    public function setDefaultSkin()
    {

        if (!$this->id)
            return 0;

        $this->deleteSkin();

        $default_skin = MCRAFT . 'tmp/default_skins/Char' . (($this->isFemale()) ? '_female' : '') . '.png';

        if (!copy($default_skin, $this->getSkinFName()))
            vtxtlog('[SetDefaultSkin] error while COPY default skin for new user.');
        else
            $this->defaultSkinTrigger(true);

        return 1;
    }

    public function changeName($newname)
    {
        global $bd_users, $site_ways;

        if (!$this->id)
            return 0;

        $newname = trim($newname);

        if (!preg_match("/^[a-zA-Z0-9_-]+$/", $newname))
            return 1401;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$this->db}` "
                . "WHERE `{$bd_users['login']}`=:login", array('login' => $newname), 'num');

        if ((int) $line[0])
            return 1402;

        if ((strlen($newname) < 4) or (strlen($newname) > 15))
            return 1403;

        getDB()->ask("UPDATE `{$this->db}` SET `{$bd_users['login']}`=:login "
                . "WHERE `{$bd_users['id']}`='{$this->id}'", array('login' => $newname));

        if (!empty($_SESSION['user_name']) and $_SESSION['user_name'] == $this->name)
            $_SESSION['user_name'] = $newname;

        /* Переименование файла скина и плаща */

        $way_tmp_old = $this->getSkinFName();
        $way_tmp_new = MCRAFT . $site_ways['skins'] . $newname . '.png';

        if (file_exists($way_tmp_old) and !file_exists($way_tmp_new))
            rename($way_tmp_old, $way_tmp_new);

        $way_tmp_old = $this->getCloakFName();
        $way_tmp_new = MCRAFT . $site_ways['cloaks'] . $newname . '.png';

        if (file_exists($way_tmp_old) and !file_exists($way_tmp_new))
            rename($way_tmp_old, $way_tmp_new);

        $buff_mini = MCRAFT . 'tmp/skin_buffer/' . $this->name . '_Mini.png';
        $buff_mini_new = MCRAFT . 'tmp/skin_buffer/' . $newname . '.png';
        $buff_skin = MCRAFT . 'tmp/skin_buffer/' . $this->name . '.png';
        $buff_skin_new = MCRAFT . 'tmp/skin_buffer/' . $newname . '.png';

        if (file_exists($buff_mini))
            rename($buff_mini, $buff_mini_new);
        if (file_exists($buff_skin))
            rename($buff_skin, $buff_skin_new);

        $this->name = $newname;

        return 1;
    }

    public function changePassword($newpass, $repass = false, $pass = false)
    {
        global $bd_users;

        if (!$this->id)
            return 0;

        if (!is_bool($repass)) {

            if (strcmp($repass, $newpass))
                return 1504;

            $regular = "/^[a-zA-Z0-9_-]+$/";

            if (!preg_match($regular, $pass) or !preg_match($regular, $newpass))
                return 1501;

            $line = getDB()->fetchRow("SELECT `{$bd_users['password']}` FROM `{$this->db}` "
                    . "WHERE `{$bd_users['id']}`='{$this->id}'", false, 'num');

            if ($line == NULL or
                !MCRAuth::checkPass(array(
                    'pass_db' => $line[0],
                    'pass' => $pass,
                    'user_id' => $this->id,
                    'user_name' => $this->name
                )))
                return 1502;
        }

        $minlen = 4;
        $maxlen = 15;
        $len = strlen($newpass);

        if (($len < $minlen) or ($len > $maxlen))
            return 1503;

        getDB()->ask("UPDATE `{$this->db}` "
                . "SET `{$bd_users['password']}`='" . MCRAuth::createPass($newpass) . "' "
                . "WHERE `{$bd_users['id']}`='{$this->id}'");

        return 1;
    }

    public function changeGroup($newgroup)
    {
        global $bd_users, $bd_names;

        $newgroup = (int) $newgroup;
        if ($newgroup < 0)
            return false;
        if ($newgroup == $this->group)
            return false;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$bd_names['groups']}` "
                . "WHERE `id`=:group", array('group' => $newgroup), 'num');

        if (!(int) $line[0])
            return false;

        getDB()->ask("UPDATE {$this->db} SET `{$bd_users['group']}`=:group "
                . "WHERE `{$bd_users['id']}`='{$this->id}'", array('group' => $newgroup));

        $this->group = $newgroup;
        $this->permissions['lvl'] = null;
        $this->lvl = $this->getPermission('lvl');

        return true;
    }

    public function changeGender($female)
    {
        global $bd_users, $config;

        $female = (int) $female;

        if ($config['p_logic'] == 'xenforo')
            $isFemale = ($female == 1) ? 'female' : 'male';
        else
            $isFemale = ($female == 1) ? 1 : 0;

        if ((int) $this->gender() == $female)
            return false;

        getDB()->ask("UPDATE {$this->db} SET `{$bd_users['female']}`='$isFemale' "
                . "WHERE `{$bd_users['id']}`='{$this->id}'");

        $this->gender = $female;
        $this->female = ($female) ? true : false;

        $this->setDefaultSkin();
        return true;
    }

    public function changeEmail($email, $verification = false)
    {
        global $bd_users;

        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email)
            return 1901;

        if ($email === $this->email) {

            if (!$verification)
                return 1;
        } else {

            $sql = "SELECT COUNT(*) FROM {$this->db} "
                    . "WHERE `{$bd_users['email']}`=:email "
                    . "AND `{$bd_users['id']}` != '{$this->id}'";

            $line = getDB()->fetchRow($sql, array('email' => $email), 'num');

            if ((int) $line[0])
                return 1902;
        }

        if ($verification) {

            $subject = lng('REG_CONFIRM') . ' ' . $_SERVER['SERVER_NAME'];
            $http_link = 'http://' . $_SERVER['SERVER_NAME'] . BASE_URL . 'register.php?id=' . $this->id() . '&verificate=' . $this->getVerificationStr();
            $message = '<html><body><p>' . lng('REG_CONFIRM_MES') . '. <a href="' . $http_link . '">' . lng('OPEN') . '</a></p></body></html>';

            $send_result = EMail::Send($email, $subject, $message);

            if ($verification and !$send_result)
                return 1903;
        }

        if ($email != $this->email)
            getDB()->ask("UPDATE {$this->db} "
                    . "SET `{$bd_users['email']}`=:email "
                    . "WHERE `{$bd_users['id']}`='{$this->id}'", array('email' => $email));

        $this->email = $email;

        return 1;
    }

    public function getSkinLink($mini = false, $amp = '&amp;', $refresh = false)
    {
        global $config;

        $use_def_skin = $this->defaultSkinTrigger();
        $name = $this->name();

        $female = ($this->isFemale()) ? true : false;

        $get_p = '?';

        if ($mini == true)
            $get_p .= 'm=1';

        if ($this->id() === false)
            return $get_p;

        if ($get_p !== '?')
            $get_p .= $amp;
        $way_skin = $this->getSkinFName();
        $way_cloak = $this->getCloakFName();

        if (($mini and $use_def_skin) or (!file_exists($way_cloak) and $use_def_skin))
            $get_p .= 'female=' . (($female) ? '1' : '0');
        else
            $get_p .= 'user_name=' . $name;

        if ($refresh)
            $get_p .= $amp . 'refresh=' . rand(1000, 9999);

        return $get_p;
    }

    public function changeVisual($post_name, $type = 'skin')
    {
        global $bd_users;

        if (!$this->id or !$this->getPermission(($type == 'skin') ? 'change_skin' : 'change_cloak'))
            return 1605;

        if (!POSTGood($post_name))
            return 1604;

        $tmp_dir = MCRAFT . 'tmp/';

        $new_file_info = POSTSafeMove($post_name, $tmp_dir);
        if (!$new_file_info)
            return 1610;

        $way = $tmp_dir . $new_file_info['tmp_name'];

        if ((int) $this->getPermission('max_fsize') < $new_file_info['size_mb'] * 1024) {

            unlink($way);
            return 1601;
        }

        loadTool('skin.class.php');

        $new_file_ratio = ($type == 'skin') ? skinGenerator2D::isValidSkin($way) : skinGenerator2D::isValidCloak($way);
        if (!$new_file_ratio or $new_file_ratio > (int) $this->getPermission('max_ratio')) {

            unlink($way);
            return 1602;
        }

        ($type == 'skin') ? $this->deleteSkin() : $this->deleteCloak();
        $new_way = ($type == 'skin') ? $this->getSkinFName() : $this->getCloakFName();

        if (rename($way, $new_way))
            chmod($new_way, 0777);
        else {

            unlink($way);
            vtxtlog('[Ошибка модуля загрузки] Ошибка копирования [' . $way . '] в [' . $new_way . '] . Проверьте доступ на ЧТЕНИЕ \ ЗАПИСЬ соответствующих папок.');
            return 1611;
        }

        if ($type == 'skin') {

            if (!strcmp($this->defaultSkinMD5(), md5_file($this->getSkinFName())))
                $this->defaultSkinTrigger(true);
            else
                $this->defaultSkinTrigger(false);
        }

        $this->deleteBuffer();

        getDB()->ask("UPDATE `{$this->db}` SET `undress_times`=`undress_times`+1 "
                . "WHERE `{$bd_users['id']}`='{$this->id}'");
        return 1;
    }

    public function Delete()
    {
        global $bd_users, $bd_names;

        if (!$this->id)
            return false;

        loadTool('catalog.class.php');

        $this->deleteCloak();
        $this->deleteSkin();
        $this->deleteBuffer();

        $result = getDB()->ask("SELECT `id` FROM `{$bd_names['comments']}` "
                . "WHERE `user_id`='{$this->id}'");

        while ($line = $result->fetch('num')) {

            $comment_del = new Comments_Item($line[0]);
            $comment_del->Delete();
            unset($comment_del);
        }

        getDB()->ask("DELETE FROM `{$this->db}` WHERE `{$bd_users['id']}`= '{$this->id}'");

        $this->id = false;
        return true;
    }

    public function getVerificationStr()
    {
        if (!$this->id)
            return false;

        $salt = sqlConfigGet('email-verification-salt');

        if (!$salt) {
            $salt = randString();
            sqlConfigSet('email-verification-salt', $salt);
        }

        return md5($this->id() . $salt);
    }

    public function getPermission($param)
    {
        if (isset($this->permissions[$param]))
            return $this->permissions[$param];
        if (!$this->id)
            return false;

        $group = new Group($this->group);
        $value = $group->GetPermission($param);

        unset($group);

        if ((int) $value == -1)
            return false;

        $this->permissions[$param] = $value;

        return $value;
    }

    public function isFemale()
    {
        return $this->female;
    }

    public function gender()
    {
        return $this->gender;
    }

    public function Exist()
    {
        if ($this->id)
            return true;
        return false;
    }

    public function id()
    {
        return $this->id;
    }

    public function lvl()
    {
        return $this->lvl;
    }

    public function tmp()
    {
        return $this->tmp;
    }

    public function ip()
    {
        return $this->ip;
    }

    public function email()
    {
        return $this->email;
    }

    public function group()
    {
        return $this->group;
    }

    public function auth_fail_num()
    {
        return $this->deadtry;
    }

    public function name()
    {
        return $this->name;
    }
}

class Group extends TextBase
{
    private $db;
    private $id;
    private $pavailable;

    public function Group($id = false, $addon = false)
    {
        global $bd_names;

        $this->db = $bd_names['groups'];
        $this->id = (int) $id;
        $this->pavailable = array(
            "change_skin",
            "change_pass",
            "lvl",
            "change_cloak",
            "change_login",
            "max_fsize",
            "max_ratio",
            "add_news",
            "adm_comm",
            "add_comm"
        );

        if (isset($bd_names['sp_skins'])) {

            $this->pavailable[] = "sp_upload";
            $this->pavailable[] = "sp_change";
        }
        if (is_array($addon))
            $this->pavailable = array_merge($this->pavailable, $addon);
    }

    public function GetPermission($param)
    {
        if (!$this->id)
            return -1;
        if (!in_array($param, $this->pavailable))
            return -1;

        $line = getDB()->fetchRow("SELECT `$param` FROM `{$this->db}` WHERE `id`='" . $this->id . "'", false, 'num');

        if ($line) {

            $value = (int) $line[0];

            if ($param != 'max_fsize' and
                $param != 'max_ratio' and
                $param != 'lvl'){
                $value = ($line[0]) ? true : false;
            }
            
            return $value;
        } else
            return -1;
    }

    public function GetAllPermissions()
    {
        $sql_names = null;

        for ($i = 0; $i < sizeof($this->pavailable); $i++)
        
            $sql_names .= ($sql_names) ? ",`{$this->pavailable[$i]}`" : "`{$this->pavailable[$i]}`";


        $line = getDB()->fetchRow("SELECT $sql_names FROM `{$this->db}` WHERE `id`='" . $this->id . "'");
        return $line;
    }

    public function Exist()
    {
        if (!$this->id)
            return false;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$this->db}` WHERE `id`='" . $this->id . "'", false, 'num');

        if ((int)$line[0])
            return true;

        $this->id = false;
        return false;
    }

    public function Create($name, &$permissions)
    {
        if ($this->id)
            return false;

        if (!$name or !TextBase::StringLen($name))
            return false;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$this->db}` "
                                . "WHERE `name`=:name", array('name' => $name), 'num');
        
        if ((int)$line[0])
            return false;

        $sql_names = null;
        $sql_vars = null;
        $sql_data = array($name);
        
        foreach ($permissions as $key => $value) {
            
            $key = array_search($key, $this->pavailable);
            if (!$key) continue;

            if ($key != 'max_fsize' and
                $key != 'max_ratio' and
                $key != 'lvl')
                $sql_perm[] = ($value) ? 1 : 0;
            else
                $sql_perm[] = (int) $value;

                $sql_names .= ",`$key`";
                $sql_vars .= ",?";
        }

        $result = getDB()->ask("INSERT INTO `{$this->db}` (`name`,$sql_names) "
                             . "VALUES (?,$sql_vars)", $sql_data);
        if ($result and $result->rowCount())
            $this->id = getDB()->lastInsertId();
        else
            return false;

        return true;
    }

    public function GetName()
    {
        $line = getDB()->fetchRow("SELECT `name` FROM `{$this->db}` "
                        . "WHERE `id`='" . $this->id . "'", false, 'num');

        if (!$line)
            return false;

        return $line[0];
    }

    public function IsSystem()
    {

        $line = getDB()->fetchRow("SELECT `system` FROM `{$this->db}` WHERE `id`='" . $this->id . "'", false, 'num');

        if (!$line)
            return false;

        return ((int)$line[0]) ? true : false;
    }

    public function Edit($name, &$permissions)
    {

        if (!$this->id)
            return false;
        if (!$name or !TextBase::StringLen($name))
            return false;

        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$this->db}` "
        . "WHERE `name`=:name and `id`!='{$this->id}'", array('name' => $name), 'num');

        if ((int)$line[0])
            return false;

        $sql = null;
        $sql_data = array($name);
        
        for ($i = 0; $i < sizeof($this->pavailable); $i++) {

            $key = $this->pavailable[$i];

            if (isset($permissions[$key])) {

                if ($key != 'max_fsize' and
                        $key != 'max_ratio' and
                        $key != 'lvl')
                    $sql_data[] = ($permissions[$key]) ? 1 : 0;
                else
                    $sql_data[] = (int) $permissions[$key];
            } else
                $sql_data[] = 0;

            $sql .= ",`$key`=?";
        }

        if (!$sql)
            $sql = '';

        $result = getDB()->ask("UPDATE `{$this->db}` SET `name`=?$sql WHERE `id`='{$this->id}'", $sql_data);
        if ($result and $result->rowCount())
            return true;

        return true;
    }

    public function Delete()
    {
        global $bd_names;

        if (!$this->id)
            return false;
        if ($this->IsSystem())
            return false;

        $result = getDB()->ask("SELECT `id` FROM `{$bd_names['users']}` "
                   . "WHERE `group`='" . $this->id . "'");
        
        if ($result) {
            
            while ($line = $result->fetch('num')) {

                $user_del = new User($line[0]);
                $user_del->Delete();
                unset($user_del);
            }
        }

        $result = getDB()->ask("DELETE FROM `{$this->db}` WHERE `id` = '" . $this->id . "' AND `system` = '0'");

        $this->id = false;
        if ($result and $result->rowCount())
            return true;

        return false;
    }
}

class GroupManager
{
    public static function GetList($selected)
    {
        global $bd_names;

        $result = getDB()->ask("SELECT `id`, `name` FROM `{$bd_names['groups']}` ORDER BY `name` DESC LIMIT 0,90");
        $group_list = '';

        while ($line = $result->fetch())
            $group_list .= '<option value="' . $line['id'] . '" ' . (($selected == $line['id']) ? 'selected' : '') . '>' . $line['name'] . '</option>';

        return $group_list;
    }

    public static function GetNameByID($id)
    {
        if (!$id or $id < 0)
            return 'Удаленный';

        $grp_item = new Group($id);
        $grp_name = $grp_item->GetName();

        unset($grp_item);

        if (!$grp_name)
            return 'Удаленный';
        else
            return $grp_name;
    }

}
