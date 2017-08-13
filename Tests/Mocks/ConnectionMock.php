<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Tests\Mocks;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;

/**
 * Mock class for Connection.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConnectionMock extends Connection
{
    /**
     * @var mixed
     */
    private $_fetchOneResult;

    /**
     * @var \Exception|null
     */
    private $_fetchOneException;

    /**
     * @var Statement|null
     */
    private $_queryResult;

    /**
     * @var DatabasePlatformMock
     */
    private $_platformMock;

    /**
     * @var int
     */
    private $_lastInsertId = 0;

    /**
     * @var array
     */
    private $_inserts = array();

    /**
     * @var array
     */
    private $_executeUpdates = array();

    /**
     * @param array                              $params
     * @param \Doctrine\DBAL\Driver              $driver
     * @param \Doctrine\DBAL\Configuration|null  $config
     * @param \Doctrine\Common\EventManager|null $eventManager
     */
    public function __construct(array $params, $driver, $config = null, $eventManager = null)
    {
        $this->_platformMock = new DatabasePlatformMock();

        parent::__construct($params, $driver, $config, $eventManager);

        // Override possible assignment of platform to database platform mock
        $this->_platform = $this->_platformMock;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return $this->_platformMock;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($tableName, array $data, array $types = array())
    {
        $this->_inserts[$tableName][] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function executeUpdate($query, array $params = array(), array $types = array())
    {
        $this->_executeUpdates[] = array('query' => $query, 'params' => $params, 'types' => $types);
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($seqName = null)
    {
        return $this->_lastInsertId;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($statement, array $params = array(), $colnum = 0, array $types = array())
    {
        if (null !== $this->_fetchOneException) {
            throw $this->_fetchOneException;
        }

        return $this->_fetchOneResult;
    }

    /**
     * {@inheritdoc}
     */
    public function query(): Statement
    {
        return $this->_queryResult;
    }

    /**
     * {@inheritdoc}
     */
    public function quote($input, $type = null)
    {
        if (is_string($input)) {
            return "'".$input."'";
        }

        return $input;
    }

    /* Mock API */

    /**
     * @param mixed $fetchOneResult
     */
    public function setFetchOneResult($fetchOneResult)
    {
        $this->_fetchOneResult = $fetchOneResult;
    }

    /**
     * @param \Exception|null $exception
     */
    public function setFetchOneException(\Exception $exception = null)
    {
        $this->_fetchOneException = $exception;
    }

    /**
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     */
    public function setDatabasePlatform($platform)
    {
        $this->_platformMock = $platform;
    }

    /**
     * @param int $id
     */
    public function setLastInsertId($id)
    {
        $this->_lastInsertId = $id;
    }

    /**
     * @param Statement $result
     */
    public function setQueryResult(Statement $result)
    {
        $this->_queryResult = $result;
    }

    /**
     * @return array
     */
    public function getInserts()
    {
        return $this->_inserts;
    }

    /**
     * @return array
     */
    public function getExecuteUpdates()
    {
        return $this->_executeUpdates;
    }

    public function reset()
    {
        $this->_inserts = array();
        $this->_lastInsertId = 0;
    }
}
