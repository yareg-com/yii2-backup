<?php


namespace yareg\backup\logic;


use yii\base\Exception;
use yii\db\Connection;

class DatabaseBackupRestorer
{
    /**
     * @var string
     */
    protected $backupFilePath;
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var string
     */
    protected $sql;
    /**
     * @var string
     */
    protected $memoryLimit;

    /**
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param Connection $connection
     * @param string $dumperClass
     * @throws Exception
     */
    public function __construct(string $backupFilePath, Connection $connection, string $memoryLimit = '')
    {
        if (!file_exists($backupFilePath))
            throw new Exception("The backup file not found.");

        $this->backupFilePath = $backupFilePath;
        $this->connection     = $connection;
        $this->memoryLimit    = $memoryLimit;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->memoryLimit !== '') {
            echo 'Setting memory limit to ' . $this->memoryLimit . PHP_EOL;
            ini_set('memory_limit', $this->memoryLimit);
        }

        $dbName = $this->connection->createCommand("SELECT DATABASE()")->queryScalar();

        $this->connection->createCommand("DROP DATABASE `{$dbName}`")->execute();
        $this->connection->createCommand("CREATE DATABASE `{$dbName}`")->execute();
        $this->connection->createCommand("USE `{$dbName}`")->execute();

        $lines = gzfile($this->backupFilePath);

        foreach ($lines as $line) {
            if (substr($line, 0, 2) === '--' || $line === '')
                continue;
            $this->sql .= $line;
        }

        $this->connection->createCommand($this->sql)->execute();

        return true;
    }
}