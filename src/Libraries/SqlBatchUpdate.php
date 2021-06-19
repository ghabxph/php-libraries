<?php namespace Gabriel\Libraries;

class SqlBatchUpdate extends SqlBatch
{
    /**
     * SQL query to execute
     */
    protected $query = 'INSERT INTO %s(%s) %s';

    /**
     * Set the table and columns for batch update
     *
     * @param string $table    Name of table
     * @param array  $columns
     * @param array  $toUpdate Columns to update
     */
    public function setFields($table, $columns, $toUpdate)
    {
        array_walk($toUpdate, function(&$item) { $item = sprintf('%s=VALUES(%s)', $item, $item);});
        $onUpdate = sprintf('ON DUPLICATE KEY UPDATE %s', implode(', ', $toUpdate));
        $this->query = sprintf($this->query, $table, implode(', ', $columns), 'VALUES %s '. $onUpdate);
    }
}
