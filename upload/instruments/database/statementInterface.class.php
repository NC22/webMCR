<?php
interface StatementInterface
{
    public function setFetchMode($mode = 'assoc');
    public function getFetchMode();
	
    /**
     * Get one row from executed query
     * @param string $mode set output mode directly in that function
     * @return mixed  Return <b>array</b> or <b>null</b> (if count of rows is zero) 
     */
    public function fetch($mode = false); 
	
    public function rowCount();   
}
