<?php

class MySqliStatement
{    
    private static $modes = array (
         'assoc' => MYSQLI_ASSOC,
         'num' => MYSQLI_NUM,
         'both' => MYSQLI_BOTH   
    );
    
    private $result = false;
    private $fetchMode = 'assoc';      
    private $affectedRows = 0;

    public function __construct($result, $affectedRows = 0)
    {
        $this->result = $result;
        $this->affectedRows = $affectedRows;
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
        
        return $this->result->fetch_array(self::$modes[$this->fetchMode]);
    }

    public function rowCount()
    {
        return $this->affectedRows;
    }

}
