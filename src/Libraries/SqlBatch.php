<?php namespace Gabriel\Libraries;

abstract class SqlBatch
{
    /**
     * SQL query to execute
     */
    protected $query;

    /**
     * Batch of items for processing
     */
    protected $queue;

    /**
     * Target number of items before batch operation will run.
     *
     * Default is 100 items
     */
    protected $count = 100;

    /**
     * Callback function to do SQL execution
     *
     * Required parameters:
     *   - query
     *   - params
     *   - types
     */
    private $executeQuery;

    /**
     * Set number of batches before execution will happen
     *
     * @param int $count Batch count
     */
    public function setBatchCount($count)
    {
        $this->count = $count;
    }

    /**
     * Set callback function to do SQL execution
     *
     * Required parameters:
     *   - query
     *   - params
     *   - types
     *
     * @param callback $executeQuery
     */
    public function setExecuteQueryCallback($executeQuery)
    {
        $this->executeQuery = $executeQuery;
    }

    /**
     * Inserts item to queue for batch processing
     *
     * @param string $placeholders
     * @param array  $params
     * @param mixed  $types
     */
    public function insertItem($placeholders, $params, $types)
    {
        $this->throwExceptionifExecuteQueryIsNotSet();
        $this->queue['placeholders'][] = $placeholders;
        $this->queue['params'][] = $params;
        $this->queue['types'][] = $types;
        $this->execute();
    }

    /**
     * Executes the batch operation either if $execute parameter is set
     * to true or the target number of batch items is reached.
     *
     * @param boolean $execute
     */
    public function execute($execute = false)
    {
        $this->throwExceptionifExecuteQueryIsNotSet();
        $canExecute = count($this->queue['placeholders']) === $this->count || $execute;
        $notEmpty = count($this->queue['placeholders']) > 0;
        if ($canExecute && $notEmpty) {
            call_user_func($this->executeQuery,
                sprintf($this->query, implode(', ', $this->queue['placeholders'])),
                $this->queue['params'],
                $this->queue['types']
            );
        }
    }

    /**
     * Throws exception if executeQuery callback is not set
     */
    private function throwExceptionifExecuteQueryIsNotSet()
    {
        if ($this->executeQuery === null) {
            throw new \Exception('Please set executeQuery callback: Run setExecuteQueryCallback method first.');
        }
    }
}
