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
    /** @var DriverConnection[] */
    protected static array $persistedConnections = [];

    /** @var int[] */
    protected static array $persistedTransactionNestingLevels = [];

    public function connect(): DriverConnection
    {
        if ($this->isConnected()) {
            return parent::connect();
        }

        if ($this->hasPersistedConnection()) {
            $this->injectDriverConnection($this->getPersistedConnection());

            return $this->getPersistedConnection();
        }

        $conn = parent::connect();
        $this->persistConnection($conn);

        return $conn;
    }

    public function beginTransaction(): void
    {
        $this->wrapTransactionNestingLevel('beginTransaction');
    }

    public function commit(): void
    {
        $this->wrapTransactionNestingLevel('commit');
    }

    public function rollBack(): void
    {
        $this->wrapTransactionNestingLevel('rollBack');
    }

    private function injectDriverConnection(DriverConnection $connection): void
    {
        static $prop = null;
        if ($prop === null) {
            $prop = new \ReflectionProperty(Connection::class, '_conn');
        }
        $prop->setValue($this, $connection);
    }

    private function setTransactionNestingLevel(int $level): void
    {
        static $prop = null;
        if ($prop === null) {
            $prop = new \ReflectionProperty(Connection::class, 'transactionNestingLevel');
        }
        $prop->setValue($this, $level);
    }

    /** @throws \Exception */
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

    protected function getPersistedTransactionNestingLevel(): int
    {
        return static::$persistedTransactionNestingLevels[$this->getConnectionId()] ?? 0;
    }

    protected function persistTransactionNestingLevel(int $level): void
    {
        static::$persistedTransactionNestingLevels[$this->getConnectionId()] = $level;
    }

    protected function persistConnection(DriverConnection $connection): void
    {
        static::$persistedConnections[$this->getConnectionId()] = $connection;
    }

    protected function hasPersistedConnection(): bool
    {
        return isset(static::$persistedConnections[$this->getConnectionId()]);
    }

    protected function getPersistedConnection(): DriverConnection
    {
        return static::$persistedConnections[$this->getConnectionId()];
    }

    protected function getConnectionId(): string
    {
        return \md5(\serialize($this->getParams()));
    }
}
