<?php namespace Gabriel\Libraries;

class SqlBatchInsert extends SqlBatch
{
    /**
     * SQL query to execute
     */
    protected $query = 'INSERT INTO %s(%s) %s';

    /**
     * Set the table and columns for batch insert
     *
     * @param string $table   Name of table
     * @param array  $columns
     */
    public function setFields($table, $columns)
    {
        $this->query = sprintf($this->query, $table, implode(', ', $columns), 'VALUES %s');
    }
}
