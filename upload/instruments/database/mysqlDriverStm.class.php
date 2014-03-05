<?php
/**
 * abstract class for mysql / mysqli statement
 */

abstract class MysqlDriverStm
{     
    protected $queryTpl = null;
    protected $dataPool = array();
    protected $fetchMode = 'assoc'; 
    
    private static $pVarCharset = "abcdefghijklmnopqrstuvwxyz0123456789_";
    
    private function parseQuery()
    {
        $asoc = null;

        $querySize = strlen($this->queryTpl);
        $utfSafeStack = array();
        $posStack = array();
        
        if (!$querySize) return $this->queryTpl;
        
        foreach ($this->dataPool as $k => $v) {

            for ($i = 0; $i < $querySize; $i++) {

                $pos = strpos($this->queryTpl, ':' . $k, $i);

                if ($pos === false)
                    continue;

                $next = $pos + strlen(':' . $k);

                if ($next < $querySize and strpos(self::$pVarCharset, $this->queryTpl[$next]) !== false)
                    continue;

                $posStack[$next] = $k;
                $this->dataPool[$k] = $this->db->quote($v);
            }
        }
        
        if (!sizeof($posStack))
            return $this->queryTpl;

        ksort($posStack, SORT_NUMERIC);

        $cursor = 0;

        foreach ($posStack as $pos => &$key) {

            $length = (int) $pos - $cursor;
            $sqlPart = substr($this->queryTpl, $cursor, $length - strlen(':' . $key)) . $this->dataPool[$key];
            $utfSafeStack[] = $sqlPart;

            $cursor = $pos;
        }

        $length = $querySize - $cursor;
        $utfSafeStack[] = substr($this->queryTpl, $cursor, $length);

        return implode('', $utfSafeStack);
    }
    
    public function bindData($data) 
    {
        if (!is_array($data)) return false;
        
        $this->dataPool = $data;
        return true;
    }
    
    public function bindValue($index, $data, $type = 'string') 
    {        
        // according to PDO specification starts from index [1], 
        // but store as usual from zero
        
        if (is_int($index)) {
            $index = ($index <= 0) ? 1 : $index-1; 
        }
                
        settype($data, $type);
        $this->dataPool[$index] = $data;
        
        return true;
    }
     
    public function setFetchMode($mode = 'assoc')
    {
        // static::$modes not work with old php module, do dynamic
        
        if (array_key_exists($mode, $this->modeList)) { 
            $this->fetchMode = $mode;
            return true;
        }

        return false;
    }
    
    public function getResult() 
    {
        return $this->result;        
    }    
     
    public function execute($data = null) 
    {
        if ($data) {
            $this->dataPool = $data;
        }
        $this->result = $this->db->queryResource($this->getQuery());

        return ($this->result === false) ? false : true;
    }   
    
    public function getQuery() 
    {        
        $queryTpl = $this->queryTpl;
        
        if (isset($this->dataPool[0])) {
            foreach ($this->dataPool as $k => &$v) {
                $queryTpl = preg_replace('/\?/', $this->db->quote($v), $queryTpl, 1);
            }
        } elseif ($this->dataPool) {
            return $this->parseQuery();
        }
        
        return $queryTpl; 
    }     
}
