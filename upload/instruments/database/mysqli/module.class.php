<?php
class MySqliDriver extends MysqlDriverBase implements DataBaseInterface
{
    public function connect($data)
    {
        $this->link = new mysqli($data['host'] . ':' . $data['port'], $data['login'], $data['password'], $data['db']);

        if (mysqli_connect_error()) {
        
            $this->lastError = 'SQLError: ' . mysqli_connect_error() . ' (' . mysqli_connect_errno() . ') ';
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
        if ($this->link){ 
            $this->link->close();        
        }
        $this->link = false;
    }
  
    public function prepare($queryTpl, $data = false)
    {
        if (!$this->link) {
            return false;
        }
        
        $statement = new MySqliStatement($this, $queryTpl);
        $statement->bindData($data);
        
        return $statement;        
    }
    
    public function queryResource($query) 
    {
            $result = $this->link->query($query, MYSQLI_STORE_RESULT);
        if ($result === false) {
            $this->log('SQLError: [' . $query . ']');
        }
        
        return $result;
    }
    
    public function query($query) 
    {        
        $statement = new MySqliStatement($this, $query);
        $statement->execute();
        
        return $statement;
    }
    
    public function quote($str, $isColName = false)
    {
        if (!$this->link) {
            return false;
        }

        $quotes = ($isColName) ? "`" : "'";

        return $quotes . $this->link->real_escape_string($str) . $quotes;
    }
    
    public function lastInsertId()
    {
        if (!$this->link)
            return false;

        return $this->link->insert_id;
    }
}
