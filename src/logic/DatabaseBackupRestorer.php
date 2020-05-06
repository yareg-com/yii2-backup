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
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param Connection $connection
     * @param string $memoryLimit
     */
    public function __construct(string $backupFilePath, Connection $connection)
    {
        if (!file_exists($backupFilePath))
            throw new Exception("The backup file not found.");

        $this->backupFilePath = $backupFilePath;
        $this->connection     = $connection;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $host   = $this->getDsnAttribute('host', $this->connection->dsn);
        $dbname = $this->getDsnAttribute('dbname', $this->connection->dsn);

        $cmd = 'gunzip < ' . $this->backupFilePath . ' | mysql -h ' . $host .
            ' -u ' . $this->connection->username .
            ' -p' . $this->connection->password .
            ' ' .$dbname;

        $output     = '';
        $return_var = -1;
        exec($cmd, $output, $return_var);

        return $return_var === 0;
    }

    /**
     * @param $name
     * @param $dsn
     * @return mixed|null
     */
    private function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        }

        return null;
    }
}