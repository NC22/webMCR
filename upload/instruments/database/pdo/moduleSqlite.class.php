<?php
class PDOSqliteDriver extends PDODriver implements DataBaseInterface
{
    public function connect($data)
    {
        if ($this->link) {
            return false;
        }

        try {
            $this->link = new PDO("sqlite:" . $data['file']);
            $this->query('PRAGMA encoding = "UTF-8";');

            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->log('SQLError: ' . $e->getMessage());
            throw new Exception($this->lastError);
            return false;
        }
        
        return true;
    }
}
