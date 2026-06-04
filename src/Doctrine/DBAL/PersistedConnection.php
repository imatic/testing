<?php declare(strict_types=1);
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
namespace Imatic\Testing\Doctrine\DBAL;

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

    public function connect(): bool
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

        $this->setNestTransactionsWithSavepoints(true);

        return true;
    }

    public function beginTransaction(): bool
    {
        $this->wrapTransactionNestingLevel('beginTransaction');

        return true;
    }

    public function commit(): bool
    {
        $this->wrapTransactionNestingLevel('commit');

        return true;
    }

    public function rollBack(): bool
    {
        $this->wrapTransactionNestingLevel('rollBack');

        return true;
    }

    private function setTransactionNestingLevel(int $level): void
    {
        static $prop = null;
        if (!$prop) {
            try {
                // before doctrine/dbal 2.9
                $prop = new \ReflectionProperty(Connection::class, '_transactionNestingLevel');
            } catch (\ReflectionException $e) {
                // since doctrine/dbal 2.9
                $prop = new \ReflectionProperty(Connection::class, 'transactionNestingLevel');
            }
        }

        $prop->setValue($this, $level);
    }

    /**
     * @param string $method
     *
     * @throws \Exception
     */
    private function wrapTransactionNestingLevel(string $method): void
    {
        $e = null;

        $this->setTransactionNestingLevel($this->getPersistedTransactionNestingLevel());

        try {
            parent::{$method}();
        } catch (\Exception $e) {
        }

        $this->persistTransactionNestingLevel($this->getTransactionNestingLevel());

        if ($e) {
            throw $e;
        }
    }

    /**
     * @param bool $connected
     *
     * @deprecated This is useless with newer versions of dbal
     */
    protected function setConnected(bool $connected): void
    {
        static $isConnected = null;
        if ($isConnected === null) {
            $connRef = new \ReflectionClass(Connection::class);
            if ($connRef->hasProperty('_isConnected')) { // before doctrine/dbal 2.9
                $isConnected = $connRef->getProperty('_isConnected');
            } elseif ($connRef->hasProperty('isConnected')) { // since doctrine/dbal 2.9
                $isConnected = $connRef->getProperty('isConnected');
            } else {
                // New versions of dbal don't have connected property.
                // They test if connection property is null.
                $isConnected = false;
            }
        }

        if ($isConnected !== false) {
            $isConnected->setValue($this, $connected);
        }
    }

    protected function getPersistedTransactionNestingLevel(): int
    {
        if (isset(static::$persistedTransactionNestingLevels[$this->getConnectionId()])) {
            return static::$persistedTransactionNestingLevels[$this->getConnectionId()];
        }

        return 0;
    }

    protected function persistTransactionNestingLevel(int $level): void
    {
        static::$persistedTransactionNestingLevels[$this->getConnectionId()] = $level;
    }

    /**
     * @param DriverConnection $connection
     */
    protected function persistConnection(DriverConnection $connection): void
    {
        static::$persistedConnections[$this->getConnectionId()] = $connection;
    }

    /**
     * @return bool
     */
    protected function hasPersistedConnection(): bool
    {
        return isset(static::$persistedConnections[$this->getConnectionId()]);
    }

    /**
     * @return DriverConnection
     */
    protected function getPersistedConnection(): DriverConnection
    {
        return static::$persistedConnections[$this->getConnectionId()];
    }

    /**
     * @return string
     */
    protected function getConnectionId(): string
    {
        return \md5(\serialize($this->getParams()));
    }
}
