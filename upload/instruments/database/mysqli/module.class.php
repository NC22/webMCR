<?php
class MySqliDriver implements DataBaseInterface
{
    public $link = false;
    private $lastError = '';
    
    public function connect($host, $port, $login, $pwd, $dbName)
    {
        $this->link = new mysqli($host . ':' . $port, $login, $pwd, $dbName);

        if (mysqli_connect_error()) {
            $this->lastError = 'SQLError: ' . mysqli_connect_error() . ' (' . mysqli_connect_errno() . ') ';
            throw new Exception($this->lastError);
            return false;
        }

        $this->ask("SET time_zone = '" . date('P') . "'");
        $this->ask("SET character_set_client='utf8'");
        $this->ask("SET character_set_results='utf8'");
        $this->ask("SET collation_connection='utf8_general_ci'");

        return true;
    }

    public function close()
    {
        if ($this->link){ 
            $this->link->close();        
        }
        $this->link = false;
    }

    public function safe($str, $isColName = false)
    {
        if (!$this->link) {
            return false;
        }

        $quotes = ($isColName) ? "`" : "'";

        return $quotes . $this->link->real_escape_string($str) . $quotes;
    }
    
    private function replaceVar($key, &$data){
        
    }
    
    public function ask($queryTpl, $data = array())
    {
        if (!$this->link) {
            return false;
        }

        $asoc = null;
        $i = 0;

        if (is_array($data)) {
            foreach ($data as $k => &$v) {
                
                $v = $this->safe($v);
                
                if ($asoc === null) {
                    $asoc = is_numeric($k) ? false : true;
                }
                
                $queryTpl = str_replace(($asoc) ? ":" . $k : "?", $v, $queryTpl, $count = 1);
            }
        }

        $result = $this->link->query($queryTpl, MYSQLI_STORE_RESULT);

        if ($result === false) {
            
            $this->lastError = 'SQLError: [' . $queryTpl . ']'; 
            
            if (function_exists('vtxtlog')) {
                vtxtlog($this->lastError);
            }

            return false;
        }

        $result = new MySqliStatement($result, $this->affectedRows());

        return $result;
    }

    public function fetchRow($queryTpl, $data = array(), $fetchMode = 'assoc')
    {
        $result = $this->ask($queryTpl, $data);

        if ($result === false) {
            return false;
        }
        
        $result->setFetchMode($fetchMode);
        $lines = $result->fetch();
        
        return $lines;
    }

    public function affectedRows()
    {
        if (!$this->link)
            return false;

        return $this->link->affected_rows;
    }

    public function getLastId()
    {
        if (!$this->link)
            return false;

        return $this->link->insert_id;
    }

    public function getLastError()
    {
        return $this->lastError;
    }
    
    public function isColumnExist($table, $column)
    {
        if (!$this->link)
            return false;

        return (@$this->ask("SELECT `$column` FROM `$table` LIMIT 0, 1")) ? true : false;
    }

    public function getColumnType($table, $column)
    {
        if (!$this->link) {
            return false;
        }

        $result = $this->fetchRow("SHOW FIELDS FROM `$table` WHERE Field =$column");

        return $result['Type'];
    }
}
