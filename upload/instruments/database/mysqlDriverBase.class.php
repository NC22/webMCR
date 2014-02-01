<?php
abstract class mysqlDriverBase
{
    private static $pVarCharset = "abcdefghijklmnopqrstuvwxyz0123456789_";

    public function parse(&$queryTpl, $data)
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
                $data[$k] = $this->quote($v);
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
    
    public function ask($queryTpl, $data = false)
    {
        if (!$this->link) {
            return false;
        }
        
        if (isset($data[0])) {
            foreach ($data as $k => &$v) {
                $queryTpl = preg_replace('/\?/', $this->quote($v), $queryTpl, 1);
            }
        } elseif (is_array($data)) {
            return $this->query($this->parse($queryTpl, $data));
        }

        return $this->query($queryTpl);        
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
