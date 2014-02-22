<?php
class MySqlDriver extends MysqlDriverBase implements DataBaseInterface
{    
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

        $this->query("SET time_zone = '" . date('P') . "'");
        $this->query("SET character_set_client='utf8'");
        $this->query("SET character_set_results='utf8'");
        $this->query("SET collation_connection='utf8_general_ci'");

        return true;
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
  
    public function prepare($queryTpl)
    {
        if (!$this->link) {
            return false;
        }
        
        return new MySqlStatement($this, $queryTpl);        
    }
    
    public function queryResource($query) 
    {
            $result = mysql_query($query, $this->link);
        if ($result === false) {
            $this->log('SQLError: [' . $query . ']');
        }
        
        return $result;
    }
    
    public function query($query) 
    {        
        $statement = new MySqlStatement($this, $query);
        $statement->execute();
        
        return $statement;
    }
    
    public function lastInsertId()
    {
        if (!$this->link) {
            return false;
        }

        return mysql_insert_id($this->link);
    }
}
