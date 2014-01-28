<?php
class MySqliDriver  extends PDOEmulator implements DataBaseInterface
{
    public $link = false;
    private $lastError = '';
    
    public function connect($data)
    {
        $this->link = new mysqli($data['host'] . ':' . $data['port'], $data['login'], $data['password'], $data['db']);

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

    public function query($query) 
    {
        $result = $this->link->query($query, MYSQLI_STORE_RESULT);

        if ($result === false) {
            
            $this->lastError = 'SQLError: [' . $query . ']'; 
            
            if (function_exists('vtxtlog')) {
                vtxtlog($this->lastError);
            }

            return false;
        }

        $result = new MySqliStatement($result, $this->link->affected_rows);

        return $result;
    }
    
    public function quote($str, $isColName = false)
    {
        if (!$this->link) {
            return false;
        }

        $quotes = ($isColName) ? "`" : "'";

        return $quotes . $this->link->real_escape_string($str) . $quotes;
    }
    
    public function ask($queryTpl, $data = array())
    {
        if (!$this->link) {
            return false;
        }

        $asoc = null;
        $i = 0;

        if (is_array($data)) {
            
            if (!isset($data[0])) {
               $query = self::parse($queryTpl, $data, $this); 
            } else {
                
                foreach ($data as $k => &$v) {
                    
                    $v = $this->quote($v);
                    $query = str_replace("?", $v, $queryTpl, $count = 1);
                }
            }
        } else $query = $queryTpl;

        return $this->query($query);
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
    
    public function lastInsertId()
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
