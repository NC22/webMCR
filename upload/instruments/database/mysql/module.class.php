<?php
class MySqlDriver extends mysqlDriverBase implements DataBaseInterface
{  
    public $link = false;
    private $lastError = '';
    
    public function connect($data)
    {
        $this->link = mysql_connect($data['host'] . ':' . $data['port'], $data['login'], $data['password']);
        
        if ($this->link === false) {
            $this->log('SQLError: connect error');
            throw new Exception($this->lastError);
            return false;            
        }
        
        if (mysql_select_db($data['db'], $this->link) === false) {
            $this->log('SQLError: select database error ');
            throw new Exception($this->lastError);
            return false;            
        }

        $this->ask("SET time_zone = '" . date('P') . "'");
        $this->ask("SET character_set_client='utf8'");
        $this->ask("SET character_set_results='utf8'");
        $this->ask("SET collation_connection='utf8_general_ci'");

        return true;
    }
    
    private function log($error)
    {
        $this->lastError = $error;

        if (function_exists('vtxtlog')) {
            vtxtlog($this->lastError);
        }
    }
    
    public function close()
    {
        mysql_close($this->link);

        $this->link = false;
    }

    public function quote($str, $isColName = false)
    {
        if (!$this->link) {
            return false;
        }
        
        $quotes = ($isColName) ? "`" : "'";
        
        $str = mysql_real_escape_string($str, $this->link);
        
        return $quotes . $str . $quotes;
    }
    
    public function getLink() 
    {
        return $this->link;
    }
    
    public function query($query) 
    {        
        $result = mysql_query($query, $this->link);
        if ($result === false) {
            
            $this->lastError = 'SQLError: [' . $query . ']'; 
            
            if (function_exists('vtxtlog')) {
                vtxtlog($this->lastError);
            }

            return false;
        }

        $result = new MySqlStatement($result, mysql_affected_rows($this->link));

        return $result;
    }
    
    public function lastInsertId()
    {
        if (!$this->link)
            return false;

        return mysql_insert_id($this->link);
    }
}
