<?php

class PDODriverStatement
{    
    private static $modes = array (
         'assoc' => PDO::FETCH_ASSOC,
         'num' => PDO::FETCH_NUM,
         'both' => PDO::FETCH_BOTH   
    );
    
    private $result = false;
    private $fetchMode = 'assoc';      

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function setFetchMode($mode = 'assoc')
    {
        if (array_key_exists($mode, self::$modes)) {
            $this->fetchMode = $mode;
            return true;
        }

        return false;
    }

    public function fetch($mode = false)
    {
        if ($mode and $mode !== $this->fetchMode) {
            $this->setFetchMode($mode);
        }
        
        $result = $this->result->fetch(self::$modes[$this->fetchMode]);
        if (!$result) {
            $this->result->closeCursor();
        }
        
        return $result;
    }

    public function rowCount()
    {
        return $this->result->rowCount();
    }
}
