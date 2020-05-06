<?php

namespace yareg\backup\logic;

use Yii;
use yii\base\Exception;

class FolderBackupMaker
{
    /**
     * @var string
     */
    protected $backupFilePath;
    /**
     * @var string
     */
    protected $targetFolder;
    /**
     * @var string
     */
    protected $chmod;

    /**
     * DatabaseBackupMaker constructor.
     * @param string $backupFilePath
     * @param string $targetFolder
     * @throws Exception
     */
    public function __construct(string $backupFilePath, string $targetFolder)
    {
        if (file_exists($backupFilePath))
            throw new Exception("Backup file exists.");

        if (!file_exists($targetFolder))
            throw new Exception("Target folder not exists.");

        $this->backupFilePath = $backupFilePath;
        $this->targetFolder = $targetFolder;
        $this->chmod = Yii::$app->getModule('backup')->chmod;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $output     = '';
        $return_var = -1;
        $command    = "tar -zcvf {$this->backupFilePath} -C {$this->targetFolder} .";

        exec($command, $output, $return_var);

        if ($return_var === 0 && $this->chmod) {
            chmod($this->backupFilePath, $this->chmod);
        }

        return $return_var === 0;
    }
}