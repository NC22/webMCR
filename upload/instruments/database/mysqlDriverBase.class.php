<?php
/**
 * abstract class for mysql / mysqli module
 */

abstract class MysqlDriverBase
{  
    protected $lastError = '';       
    protected $link = false;
    
    public function fetchRow($queryTpl, $data = array(), $fetchMode = 'assoc')
    {
        $statement = $this->ask($queryTpl, $data);

        if ($statement === false) {
            return false;
        }
        
        $statement->setFetchMode($fetchMode);        
        $lines = $statement->fetch();
        
        return $lines;
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
    
    public function getLastError()
    {
        return $this->lastError;
    } 
    
    public function getLink() 
    {
        return $this->link;
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

        $result = $this->fetchRow("SHOW FIELDS FROM `$table` WHERE Field = '$column'");

        return $result['Type'];
    }
    
    public function log($error)
    {
        $this->lastError = $error;

        if (function_exists('vtxtlog')) {
            vtxtlog($this->lastError);
        }
    }
}
