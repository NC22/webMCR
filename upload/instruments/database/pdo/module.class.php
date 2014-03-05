<?php
class PDODriver implements DataBaseInterface
{
    /**
     *
     * @var PDO
     */
    public $link = false;
    private $lastError = '';

    public function connect($data)
    {
        if ($this->link) {
            return false;
        }

        try {
            $this->link = new PDO("mysql:host={$data['host']};port={$data['port']};dbname={$data['db']}", $data['login'], $data['password']);

            $this->query("SET time_zone = '" . date('P') . "'");
            $this->query("SET character_set_client='utf8'");
            $this->query("SET character_set_results='utf8'");
            $this->query("SET collation_connection='utf8_general_ci'");
            
            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->log('SQLError: ' . $e->getMessage());
            throw new Exception($this->lastError);
            return false;
        }
        
        return true;
    }

    public function close()
    {
        $this->link = null;
    }

    public function quote($str)
    {
        if (!$this->link) {
            return false;
        }

        return $this->link->quote($str);
    }
    
    public function log($error)
    {
        $this->lastError = $error;

        if (function_exists('vtxtlog')) {
            vtxtlog($this->lastError);
        }
    }

    public function query($query) 
    {
        try {                
            
            $statement = $this->link->query($query); 
            return new PDODriverStatement($this, $statement);
            
        } catch (PDOException $e) {

            $this->log('SQLError: [' . $e->getMessage() . '] ');
            return false;
        }
    }
    
    public function ask($queryTpl, $data = null) 
    {
        $statement = $this->prepare($queryTpl);
        if (!$statement) return false;
        
        if ($data) {
            $statement->bindData($data);
        }
        if (!$statement->execute()) return false;
        
        return $statement;
    }
    
    public function prepare($queryTpl)
    {
        if (!$this->link) {
            return false;
        }

        try {                
            
            $pdoStatement = $this->link->prepare($queryTpl);  
            return new PDODriverStatement($this, $pdoStatement);
            
        } catch (PDOException $e) {

            $this->log('SQLError: [' . $e->getMessage() . '] ' . $queryTpl);
            return false;
        }
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

        return $this->link->lastInsertId();
    }

    public function getLastError()
    {
        return $this->lastError;
    }
    
    public function isColumnExist($table, $column)
    {
        if (!$this->link)
            return false;

        return (@$this->query("SELECT `$column` FROM `$table` LIMIT 0, 1")) ? true : false;
    }

    public function getColumnType($table, $column)
    {
        if (!$this->link) {
            return false;
        }

        $result = $this->fetchRow("SHOW FIELDS FROM `$table` WHERE Field = '$column'");

        return $result['Type'];
    }
}
