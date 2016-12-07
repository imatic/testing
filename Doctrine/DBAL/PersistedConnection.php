<?php
/**
Copyright (C) 2016 Maksim Kotlyar

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
**/

namespace Imatic\Bundle\TestingBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as DriverConnection;

/**
 * @see https://gist.github.com/makasim/f1e28c9bc9458f20f38f
 *
 * Connection wrapper sharing the same db handle across multiple requests
 *
 * Allows multiple Connection instances to run in the same transaction
 */
class PersistedConnection extends Connection
{
    /**
     * @var DriverConnection[]
     */
    protected static $persistedConnections;

    /**
     * @var int[]
     */
    protected static $persistedTransactionNestingLevels;

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return false;
        }

        if ($this->hasPersistedConnection()) {
            $this->_conn = $this->getPersistedConnection();
            $this->setConnected(true);
        } else {
            parent::connect();
            $this->persistConnection($this->_conn);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        $this->wrapTransactionNestingLevel('beginTransaction');
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        $this->wrapTransactionNestingLevel('commit');
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        $this->wrapTransactionNestingLevel('rollBack');
    }

    /**
     * @param int $level
     */
    private function setTransactionNestingLevel($level)
    {
        $prop = new \ReflectionProperty('Doctrine\DBAL\Connection', '_transactionNestingLevel');
        $prop->setAccessible(true);

        return $prop->setValue($this, $level);
    }

    /**
     * @param string $method
     *
     * @throws \Exception
     */
    private function wrapTransactionNestingLevel($method)
    {
        $e = null;

        $this->setTransactionNestingLevel($this->getPersistedTransactionNestingLevel());

        try {
            call_user_func(array('parent', $method));
        } catch (\Exception $e) {
        }

        $this->persistTransactionNestingLevel($this->getTransactionNestingLevel());

        if ($e) {
            throw $e;
        }
    }

    /**
     * @param bool $connected
     */
    protected function setConnected($connected)
    {
        $isConnected = new \ReflectionProperty('Doctrine\DBAL\Connection', '_isConnected');
        $isConnected->setAccessible(true);
        $isConnected->setValue($this, $connected);
        $isConnected->setAccessible(false);
    }

    /**
     * @return int
     */
    protected function getPersistedTransactionNestingLevel()
    {
        if (isset(static::$persistedTransactionNestingLevels[$this->getConnectionId()])) {
            return static::$persistedTransactionNestingLevels[$this->getConnectionId()];
        }

        return 0;
    }

    /**
     * @param int $level
     */
    protected function persistTransactionNestingLevel($level)
    {
        static::$persistedTransactionNestingLevels[$this->getConnectionId()] = $level;
    }

    /**
     * @param DriverConnection $connection
     */
    protected function persistConnection(DriverConnection $connection)
    {
        static::$persistedConnections[$this->getConnectionId()] = $connection;
    }

    /**
     * @return bool
     */
    protected function hasPersistedConnection()
    {
        return isset(static::$persistedConnections[$this->getConnectionId()]);
    }

    /**
     * @return DriverConnection
     */
    protected function getPersistedConnection()
    {
        return static::$persistedConnections[$this->getConnectionId()];
    }

    /**
     * @return string
     */
    protected function getConnectionId()
    {
        return md5(serialize($this->getParams()));
    }
}
