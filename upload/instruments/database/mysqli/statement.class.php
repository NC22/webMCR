<?php

class MySqliStatement extends MysqlDriverStm implements StatementInterface
{    
    protected static $modes = array (
        'assoc' => MYSQLI_ASSOC,
        'num' => MYSQLI_NUM,
        'both' => MYSQLI_BOTH   
    );
    
    protected $result = false;

    /**
     *
     * @var MySqliDriver 
     */
    protected $db;
    
    /**
     * @param MySqliDriver $dbHandler current database driver
     * @param string $queryTpl query string for MySQL database
     */
    public function __construct($dbHandler, $queryTpl)
    {
        $this->queryTpl = $queryTpl;
        $this->db = $dbHandler;
        $this->modeList = self::$modes;
    }
    
    public function fetch($mode = null)
    {
        if ($mode and $mode !== $this->fetchMode) {
            $this->setFetchMode($mode);
        }
        
        if ($this->result === false and !$this->execute()) {
            return false;
        } 
        
        return $this->result->fetch_array($this->modeList[$this->fetchMode]);
    }

    public function rowCount()
    {
        return $this->db->getLink()->affected_rows;
    }
}
