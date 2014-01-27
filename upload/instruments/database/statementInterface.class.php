<?php
interface StatementInterface
{
    public function setFetchMode($mode = 'assoc');
    public function getFetchMode();
	
    /**
     * Get one row from executed query
     * @param string $mode set output mode directly
     * @return mixed return <b>array</b> or <b>null</b> (if count of rows is zero) 
     */
    public function fetch($mode = false); 
    
    /**
     * Get count of affected rows
     * @return int 
     */	
    public function rowCount();   
}
