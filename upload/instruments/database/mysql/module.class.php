<?php
class MySqlDriver implements DataBaseInterface
{
    private static $pVarCharset = "abcdefghijklmnopqrstuvwxyz0123456789_";
    
    public $link = false;
    private $lastError = '';
    
    public function connect($host, $port, $login, $pwd, $dbName)
    {
        $this->link = mysql_connect($host.':'.$port, $login, $pwd);
        
        if ($this->link === false) {
            $this->log('SQLError: connect error');
            throw new Exception($this->lastError);
            return false;            
        }
        
        if (mysql_select_db($dbName, $this->link) === false) {
            $this->log('SQLError: select database error ');
            throw new Exception($this->lastError);
            return false;            
        }

        $this->ask("SET time_zone = '" . date('P') . "'");
        $this->ask("SET character_set_client='utf8'");
        $this->ask("SET character_set_results='utf8'");
        $this->ask("SET collation_connection='utf8_general_ci'");

        return true;
    }
    
    private function log($error)
    {
        $this->lastError = $error;

        if (function_exists('vtxtlog')) {
            vtxtlog($this->lastError);
        }
    }
    
    public function close()
    {
        mysql_close($this->link);

        $this->link = false;
    }

    public function safe($str, $isColName = false)
    {
        if (!$this->link) {
            return false;
        }
        
        $quotes = ($isColName) ? "`" : "'";
        
        $str = mysql_real_escape_string($str, $this->link);
        
        return $quotes . $str . $quotes;
    }
    
    public function getLink() 
    {
        return $this->link;
    }

    private function parseLikePDO(&$queryTpl, $data)
    {
        $asoc = null;

        $querySize = strlen($queryTpl);
        $utfSafeStack = array();
        $posStack = array();
        
        if (!$querySize) return $queryTpl;
        
        foreach ($data as $k => $v) {

            for ($i = 0; $i < $querySize; $i++) {

                $pos = strpos($queryTpl, ':' . $k, $i);

                if ($pos === false)
                    continue;

                $next = $pos + strlen(':' . $k);

                if ($next < $querySize and strpos(self::$pVarCharset, $queryTpl[$next]) !== false)
                    continue;

                $posStack[$next] = $k;
                $data[$k] = $this->safe($v);
            }
        }
        
        if (!sizeof($posStack))
            return $queryTpl;

        ksort($posStack, SORT_NUMERIC);

        $cursor = 0;

        foreach ($posStack as $pos => &$key) {

            $length = (int) $pos - $cursor;
            $sqlPart = substr($queryTpl, $cursor, $length - strlen(':' . $key)) . $data[$key];
            $utfSafeStack[] = $sqlPart;

            $cursor = $pos;
        }

        $length = $querySize - $cursor;
        $utfSafeStack[] = substr($queryTpl, $cursor, $length);

        return implode('', $utfSafeStack);
    }

    public function ask($queryTpl, $data = array())
    {
        if (!$this->link) {
            return false;
        }

        $asoc = null;
        $i = 0;

        if (is_array($data)) {
            
            if (!isset($data[0])) {
               $query = $this->parseLikePDO($queryTpl, $data); 
            } else {
                
                foreach ($data as $k => &$v) {
                    
                    $v = $this->safe($v);
                    $query = str_replace("?", $v, $queryTpl, $count = 1);
                }
            }
        } else $query = $queryTpl;
        
        $result = mysql_query($query, $this->link);
        if ($result === false) {
            
            $this->lastError = 'SQLError: [' . $query . ']'; 
            
            if (function_exists('vtxtlog')) {
                vtxtlog($this->lastError);
            }

            return false;
        }

        $result = new MySqlStatement($result, mysql_affected_rows($this->link));

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

        return mysql_insert_id($this->link);
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
