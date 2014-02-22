<?php

class MySqlStatement extends MysqlDriverStm implements StatementInterface
{    
    protected static $modes = array (
        'assoc' => MYSQL_ASSOC,
        'num' =>  MYSQL_NUM,
        'both' => MYSQL_BOTH   
    );
    
    protected $result = false;

    /**
     *
     * @var MySqlDriver 
     */
    protected $db;
    
    /**
     * @param MySqlDriver $dbHandler current database driver
     * @param string $queryTpl query string for MySQL database
     */
    public function __construct($dbHandler, $queryTpl)
    {
        $this->queryTpl = $queryTpl;
        $this->db = $dbHandler;
        $this->modeList = self::$modes;
    }
    
    public function fetch($mode = false)
    {
        if ($mode and $mode !== $this->fetchMode) {
            $this->setFetchMode($mode);
        }
        
        if ($this->result === false and !$this->execute()) {
            return false;
        } 
        
        $result = mysql_fetch_array($this->result, $this->modeList[$this->fetchMode]);
        if ($result === false) return null;
        else return $result;
    }

    public function rowCount()
    {
        return mysql_affected_rows($this->db->getLink());
    }
}
