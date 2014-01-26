<?php

class MySqlStatement
{    
    private static $modes = array (
         'assoc' => MYSQL_ASSOC,
         'num' =>  MYSQL_NUM,
         'both' => MYSQL_BOTH   
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

    public function getResult() 
    {
        return $this->result;        
    }    
    
    public function fetch($mode = false)
    {
        if ($mode and $mode !== $this->fetchMode) {
            $this->setFetchMode($mode);
        }
        
        $result = mysql_fetch_array($this->result, self::$modes[$this->fetchMode]);
        if ($result === false) return null;
        else return $result;
    }

    public function rowCount()
    {
        return $this->affectedRows;
    }

}
