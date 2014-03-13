<?php

class PDODriverStatement implements StatementInterface
{    
    private static $modes = array (
        'assoc' => PDO::FETCH_ASSOC,
        'num' => PDO::FETCH_NUM,
        'both' => PDO::FETCH_BOTH   
    ); 
    
    private $fetchMode = 'assoc';     
    private $dataPool = array(); 
    private $statement = false;

    /**
     * @var PDODriver 
     */
    protected $db;
    
    /**
     * @param PDODriver $dbHandler current database driver
     * @param resource $statement prepared statement
    */
    
    public function __construct($dbHandler, $statement)
    {
        $this->statement = $statement;
        $this->db = $dbHandler;
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
        
        $result = $this->statement->fetch(self::$modes[$this->fetchMode]);
        if (!$result) {
            $this->statement->closeCursor();
        }
        
        if ($result === false) $result = null;
        
        return $result;
    }
    
    public function bindValue($index, $data, $type = 'string') 
    {        
        if (is_int($index)) {
            $index = ($index <= 0) ? 1 : $index-1; 
        }
        
        settype($data, $type);
        $this->dataPool[$index] = $data;
        
        return true;
    } 
    
    public function bindData($data) 
    {
        if (!is_array($data)) return false;
        
        $this->dataPool = $data;
        return true;
    }
    
    public function rowCount()
    {
        return $this->statement->rowCount();
    }
    
    public function execute($data = null)
    {   
        if ($this->statement === false) {
            return false;
        }
        
        try {
            if ($data) {
                return $this->statement->execute($data);
            }

            return $this->statement->execute($this->dataPool);
         }
         catch( PDOException $e )
         {
             ob_start();
             $this->statement->debugDumpParams();
             $this->db->log('[' . $e->getMessage() . '] PDOStatementDump : ' . ob_get_clean());
             return false;
         }
    }
}
