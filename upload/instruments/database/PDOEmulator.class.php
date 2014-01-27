<?php
class PDOEmulator
{
    private static $pVarCharset = "abcdefghijklmnopqrstuvwxyz0123456789_";

    public static function parse(&$queryTpl, $data, $dbInterface)
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
                $data[$k] = $dbInterface->safe($v);
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
}
