<?php

/* WEB-APP : WebMCR (С) 2013-2014 NC22 | License : GPLv3 */

if (!defined('MCR'))
    exit;

Class File extends View
{
    private static $formats = array(
        'jpg',
        'jpeg',
        'gif',
        'png',
        'zip',
        'rar',
        'exe',
        'jar',
        'doc',
        'pdf',
        'txt'
    );
    
    private $id = false;
    
    private $db;
    private $base_dir;    
    
    private $id_word;
    private $user_id;
    private $way;
    private $name;
    private $size;
    private $hash;
    private $downloads;

    public function File($id = false, $style_sd = false)
    {
        global $bd_names;

        parent::View($style_sd);

        $this->base_dir = MCRAFT . 'userdata/';
        $this->db = $bd_names['files'];

        $search_by = 'id';
        $search_var = (int) $id;

        if (is_numeric($id)) {

            $this->id = (int) $id;
            if (!$this->id)
                return false;
        } elseif ($id) {

            if (!preg_match("/^[a-zA-Z0-9._-]+$/", $id))
                return false;

            $search_var = $id;
            $search_by = 'id_word';
        } else return false;

        $line = getDB()->fetchRow("SELECT `id`, `user_id`, `id_word`, `way`, `name`, `size`, `hash`, `downloads`  "
                   . "FROM `{$this->db}` WHERE `$search_by`=:var", array('var' => $search_var), 'num');

        if (!$line) {

            $this->id = false;
            return false;
        }
        
        $this->id = (int) $line[0];
        $this->user_id = (int) $line[1];
        $this->id_word = $line[2];
        $this->way = $this->base_dir . $line[3];
        $this->name = $line[4];
        $this->size = $line[5];
        $this->hash = $line[6];
        $this->downloads = $line[7];
    }

    public function Create($post_name, $user_id, $id_word = null, $id_rewrite = false)
    {

        $user_id = (int) $user_id;
        if (!POSTGood($post_name, self::$formats))
            return 1;
        if ($id_word and !preg_match("/^[a-zA-Z0-9._-]+$/", $id_word))
            return 3;

        $new_file_info = POSTSafeMove($post_name, $this->base_dir);
        if (!$new_file_info)
            return 2;

        $way = $this->base_dir . $new_file_info['tmp_name'];
        $hash = md5_file($this->base_dir . $new_file_info['tmp_name']);

        $sql_part = ($id_word) ? " OR `id_word`=:id_word" : '';
        $data = ($id_word) ? array('id_word' => $id_word) : false;

        $line = getDB()->fetchRow("SELECT `id` FROM `{$this->db}` "
                                . "WHERE `hash`='" . $hash . "'" . $sql_part, $data, 'num');
        
        if ($line) {

            $file_similar = new File($line[0]);

            $similar_info = $file_similar->getInfo();

            if ($similar_info['hash'] == $hash) {

                if (file_exists($way))
                    unlink($way);

                $this->id = $similar_info['id'];
                $this->user_id = $similar_info['user_id'];
                $this->id_word = $similar_info['id_word'];
                $this->name = $similar_info['name'];
                $this->size = $similar_info['size'];
                $this->hash = $similar_info['hash'];
                $this->downloads = $similar_info['downloads'];

                $this->way = $file_similar->getWay();
                return 7;
            } else {

                if (!$id_rewrite) {

                    if (file_exists($way))
                        unlink($way);
                    return 4;
                } else {

                    if (!$file_similar->Delete())
                        return 6;
                    unset($file_similar);
                }
            }
        }
        
        $sql = "INSERT INTO {$this->db} (id_word, user_id, way, name, size, hash) "
                . "VALUES (:id_word, :user_id, :fway, :fname, :fsize, '$hash')";
        
        $result = getDB()->ask($sql, array(
            'id_word' => ($id_word) ? $id_word : '',
            'user_id' => $user_id,
            'fway' => $new_file_info['tmp_name'],
            'fname' => $new_file_info['name'],
            'fsize'  => $new_file_info['size_mb'] 
        ));
        
        if ($result) {

            $this->id = getDB()->lastInsertId();
            $this->user_id = $user_id;
            $this->id_word = ($id_word) ? $id_word : '';
            $this->way = $way;
            $this->name = $new_file_info['name'];
            $this->size = $new_file_info['size_mb'];
            $this->hash = $hash;
            $this->downloads = 0;
            
        } else {

            if (file_exists($way))
                unlink($way);
            return 5;
        }

        return 0;
    }

    public function Download()
    {

        if (!$this->Exist())
            return false;

        if (!file_exists($this->way))
            getDB()->ask("DELETE FROM `{$this->db}` WHERE `id`='`{$this->id}`'");

        $extension = strtolower(substr($this->name, 1 + strrpos($this->name, ".")));
        $mimetype = 'application/x-' . $extension;

        // MIME type list http://webdesign.about.com/od/multimedia/a/mime-types-by-content-type.htm
        $image = false;

        switch ($extension) {
            case 'jpg':
            case 'jpeg': $mimetype = 'image/jpeg';
                $image = true;
                break;
            case 'png': $mimetype = 'image/png';
                $image = true;
                break;
            case 'gif': $mimetype = 'image/gif';
                $image = true;
                break;
            case 'zip': $mimetype = 'application/zip';
                break;
            case 'rar': $mimetype = 'application/x-rar-compressed';
                break;
            case 'exe': $mimetype = 'application/octet-stream';
                break;
            case 'jar': $mimetype = 'application/x-jar';
                break;
            case 'pdf': $mimetype = 'application/pdf';
                break;
            case 'doc': $mimetype = 'application/msword';
                break;
            case 'txt': $mimetype = 'text/plain';
                break;
        }

        $name_enc = urlencode($this->name);

        header('Content-Type: ' . $mimetype);

        if (!$image) {

            header('Cache-Control:no-cache, must-revalidate');
            header('Expires:0');
            header('Pragma:no-cache');
            header('Content-Length:' . filesize($this->way));
            header('Content-Disposition: attachment; filename="' . $name_enc . '"');
            header('Content-Transfer-Encoding:binary');

            getDB()->ask("UPDATE `{$this->db}` SET downloads = downloads + 1 WHERE `id`='{$this->id}'");
        }

        readfile($this->way);
        return true;
    }

    public function Exist()
    {
        if ($this->id)
            return true;
        return false;
    }

    public function getWay()
    {
        if (!$this->Exist())
            return false;
        return $this->way;
    }

    public function getInfo()
    {
        if (!$this->Exist())
            return false;

        return array('id' => $this->id,
            'id_word' => $this->id_word,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'size' => $this->size,
            'downloads' => $this->downloads,
            'hash' => $this->hash);
    }

    public function Show()
    {
        global $config;

        if (!$this->Exist())
            return '';

        $file_info = $this->getInfo();

        $file_id = $file_info['id'];
        $file_word = $file_info['id_word'];

        $file_name = $file_info['name'];
        $file_size = $file_info['size'];
        $file_hash = $file_info['hash'];

        $file_downloads = $file_info['downloads'];

        $file_link_id = ($file_word) ? $file_word : $file_id;

        $file_link = 'http://' . $_SERVER['SERVER_NAME'] . BASE_URL . (($config['rewrite']) ? 'get/' : 'action.php?method=download&file=') . $file_link_id;

        ob_start();
        include $this->GetView('file.html');

        return ob_get_clean();
    }

    public function Delete()
    {

        if (!$this->Exist())
            return false;
        if (file_exists($this->way))
            unlink($this->way);

        getDB()->ask("DELETE FROM `{$this->db}` WHERE `id`='" . $this->id . "'");
        $this->id = false;

        return true;
    }

}

Class FileManager extends View
{

    private $work_skript;
    private $db;

    public function FileManager($style_sd = false, $work_skript = 'index.php?mode=control&do=filelist&')
    {
        global $bd_names;

        $this->db = $bd_names['files'];
        $this->work_skript = $work_skript;

        parent::View($style_sd);
    }

    public function ShowAddForm()
    {

        return $this->ShowPage('file_add.html');
    }

    public function ShowFilesByUser($list = 1, $user_id = false)
    {
        $list = (int) $list;
        if ($list <= 0)
            $list = 1;

        $sql_part = '';
        
        if ($user_id !== false) {
            $user_id = (int) $user_id;
            $sql_part = " WHERE `user_id`='$user_id'";
        }
    
        
        $line = getDB()->fetchRow("SELECT COUNT(*) FROM `{$this->db}`" . $sql_part, false, 'num');

        $num = (int)$line[0];

        $html_files = $this->ShowPage('files_header.html');

        if (!$num) {

            $html_files .= $this->ShowPage('files_empty.html');
            return $html_files;
        }

        $result = getDB()->ask("SELECT `id` FROM `{$this->db}`" . $sql_part . " ORDER BY `id` DESC LIMIT " . (10 * ($list - 1)) . ",10");

        if (!$result)
            return $html_files;

        while ($line = $result->fetch('num')) {

            $file = new File($line[0], $this->st_subdir);
            $html_files .= $file->Show();
        }

        $html_files .= $this->arrowsGenerator($this->work_skript, $list, $num, 10);
        return $html_files;
    }

}
