<?php
class PDODriver implements DataBaseInterface
{
    public $link = false;
    private $lastError = '';

    public function connect($data)
    {
        if ($this->link) {
            return false;
        }

        try {
            if (isset($data['file'])) {
                
                $this->link = new PDO("sqlite:" . $data['file']);
                $this->ask('PRAGMA encoding = "UTF-8";');
                
            } else {

                $this->link = new PDO("mysql:host={$data['host']};dbname={$data['db']}", $data['login'], $data['password']);

                $this->ask("SET time_zone = '" . date('P') . "'");
                $this->ask("SET character_set_client='utf8'");
                $this->ask("SET character_set_results='utf8'");
                $this->ask("SET collation_connection='utf8_general_ci'");
            }

            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->log('SQLError: ' . $e->getMessage());
            return false;
        }



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

    public function query($query) 
    {
        $statement = $this->link->query($query); 
        if ($statement === false) {
            
            $this->log('SQLError: [' . $query . ']');
            return false;
        }

        $result = new PDODriverStatement($statement);  
        return $result;
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
   
            if ($statement === false) {
                return false;
            }

            $result = new PDODriverStatement($statement);
            return $result;   
            
        } else {            
            return $this->query($queryTpl);             
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
