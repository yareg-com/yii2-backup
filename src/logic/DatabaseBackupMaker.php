<?php


namespace yareg\backup\logic;


use Ifsnop\Mysqldump\Mysqldump;
use Yii;
use yii\base\Exception;
use yii\db\Connection;

class DatabaseBackupMaker
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
    protected $dumperClass;
    /**
     * @var Mysqldump
     */
    protected $dumper;
    /**
     * @var string
     */
    protected $compression;

    /**
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param Connection $connection
     * @param string $dumperClass
     * @throws Exception
     */
    public function __construct(
        string $backupFilePath,
        Connection $connection,
        string $dumperClass = Mysqldump::class,
        string $compression = Mysqldump::NONE)
    {
        if (file_exists($backupFilePath))
            throw new Exception("Backup file exists.");

        $this->backupFilePath = $backupFilePath;
        $this->connection = $connection;
        $this->dumperClass = $dumperClass;
        $this->compression = $compression;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $this->dumper = new $this->dumperClass
        (
            $this->connection->dsn,
            $this->connection->username,
            $this->connection->password,
            ['compress' => $this->compression]
        );
        $this->dumper->start($this->backupFilePath);
        if (Yii::$app->getModule('backup')->chmod)
            chmod($this->backupFilePath, Yii::$app->getModule('backup')->chmod);
        return true;
    }
}