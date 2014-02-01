<?php
 /** 
 * @category  MySQL Database Tools v1.0
 * @package   Kelly\DataBase
 * @author    Rubchuk Vladimir <torrenttvi@gmail.com>
 * @copyright 2014 Rubchuk Vladimir
 * @license   GPLv3
 */

interface StatementInterface
{
    /**
     * Set fetch mode from one of available equivalents : <br>
     * 'assoc' => PDO::FETCH_ASSOC <br>
     * 'num' => PDO::FETCH_NUM <br>
     * 'both' => PDO::FETCH_BOTH  <br>
     * @return boolean
     */
    public function setFetchMode($mode = 'assoc');

    /**
     * Get one row from executed query
     * @param string $mode set output mode directly, give same effect as <b>setFetchMode</b>
     * @return mixed return <b>array</b> or <b>null</b> (if count of rows is zero) 
     */
    public function fetch($mode = null); 
    
    /**
     * Get count of affected rows
     * @return int 
     */	
    public function rowCount();   
    
    /**
     * @param type $data An array of values with as many elements as there are bound parameters 
     * in the SQL statement being executed
     */
    
    public function execute($data = null);
    
    public function bindValue($index, $data, $type = 'string');    
    public function bindData($data);  
}
