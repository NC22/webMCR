<?php
class PDODriver implements DataBaseInterface
{
    public $link = false;
    private $lastError = '';

    public function connect($host, $port, $login, $pwd, $dbName)
    {
        try {
            
            $this->link = new PDO("mysql:host=$host;dbname=$dbName", $login, $pwd);

        } catch (PDOException $e) {
           $this->log('SQLError: ' . $e->getMessage());
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
        $this->link = null;
    }

    public function safe($str)
    {
        if (!$this->link) {
            return false;
        }

        return $this->link->quote($str);
    }
    
    private function log($error)
    {
        $this->lastError = $error;

        if (function_exists('vtxtlog')) {
            vtxtlog($this->lastError);
        }
    }

    public function ask($queryTpl, $data = array())
    {
        if (!$this->link) {
            return false;
        }

        if (is_array($data)) {
            
            try {
                $statement = $this->link->prepare($queryTpl);
                $statement->execute($data);
                
            } catch (PDOException $e) {
                
                $this->log('SQLError: [' . $e->getMessage() . '] ' . $queryTpl);
                return false;
            }
            
        } else {
            
            $statement = $this->link->query($queryTpl);             
        }

        if ($statement === false) {
            
            $this->log('SQLError: [' . $queryTpl . ']');
            return false;
        }

        $result = new PDODriverStatement($statement);

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
    
    public function getLastId()
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
