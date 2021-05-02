<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 14:45
 */

namespace yareg\backup;

use Yii;
use yii\base\ErrorException;
use yii\base\NotSupportedException;
use yii\db\Connection;
use yii\db\Exception;
use yii\web\ForbiddenHttpException;

class Module extends \yii\base\Module
{
    /**
     * @var boolean
     */
    public $allowWebAccess = false;

    /**
     * @var string
     */
    public $administratorRoleName = 'admin';
    /**
     * @var string
     */
    public $backupFolder = '@app/backups';
    /**
     * @var string
     */
    public $chmod;
    /**
     * @var array
     */
    public $authTokens = [];
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'yareg\backup\controllers';
    /**
     * @var string
     */
    public $backupRootPath;
    /**
     * @var string
     */
    public $connection;
    /**
     * @var string
     */
    public $ionice;
    /**
     * @var array
     */
    public $configs = [];
    /**
     * @var string
     */
    public $adminLayout = '@app/views/layouts/main';

    /**
     * @inheritdoc
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ForbiddenHttpException
     */
    public function init()
    {
        if (!Yii::$app->request->isConsoleRequest && !(Yii::$app->getModule('backup')->allowWebAccess ?? false)) {
            throw new ForbiddenHttpException();
        }

        $this->backupRootPath = Yii::getAlias($this->backupFolder);

        if (!file_exists($this->backupRootPath)) {
            mkdir($this->backupRootPath);
        }

        if (!is_writable($this->backupRootPath)) {
            throw new ErrorException("Backup folder is not writable.");
        }

        $this->checkDb();
        $this->registerTranslations();

        parent::init();
    }

    /**
     * @param string $config_id
     * @return bool
     */
    public function checkConfig(string $config_id): bool
    {
        foreach (Yii::$app->getModule('backup')->configs as $config) {
            if ($config['id'] == $config_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws NotSupportedException
     * @throws Exception
     */
    public function checkDb()
    {
        $dbFileName = $this->backupRootPath . '/sqlite.db';
        $this->connection = new Connection(['dsn' => 'sqlite:' . $dbFileName]);
        $this->connection->getSchema();
        $this->connection->createCommand('
            CREATE TABLE IF NOT EXISTS backup (
              id INTEGER PRIMARY KEY,
              date DATETIME NOT NULL,
              status INTEGER NOT NULL DEFAULT 0,
              type INTEGER NOT NULL,
              config_id STRING(255) NOT NULL,
              config_name STRING(255) NULL,
              filename STRING(255) NULL,
              size INTEGER NOT NULL DEFAULT 0              
            );            
        ')->execute();
    }

    /**
     * Register some lang files
     * @return void
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['app.f12.backup'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => '@vendor/yareg-com/yii2-backup/src/messages',
            'sourceLanguage' => 'en-US',
            'fileMap' => [
                'app.f12.backup' => 'backup.php',
            ],
        ];
    }
}