<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 18.09.2018
 * Time: 20:34
 */

namespace yareg\backup\controllers;

use ErrorException;
use Throwable;
use yareg\backup\logic\BackupCreate;
use yareg\backup\logic\BackupRestore;
use yareg\backup\models\Backup;
use yareg\backup\models\BackupType;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\StaleObjectException;
use yii\helpers\Console;

/**
 * Backup module console controller
 *
 * Class ConsoleController
 * @package yareg\backup\controllers
 */
class ConsoleController extends Controller
{
    /**
     * Pass config_id to this command to create new backup.
     *
     * @param string $config_id
     * @throws InvalidConfigException|StaleObjectException|Throwable
     */
    public function actionBackup(string $config_id)
    {
        Yii::createObject(BackupCreate::class, [$config_id])->run();
        $this->stdout('Backup created.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Pass existing backup ID to this command to restore from backup.
     *
     * @param string $backup_id
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function actionRestore(string $backup_id)
    {
        $model = Backup::findOne((int)$backup_id);
        if (!$model)
            throw new ErrorException('Backup not found.');

        Yii::createObject(BackupRestore::class, [$model])->run();
        $this->stdout('Backup restored.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Pass existing backup ID and filename to this command to restore from specific file.
     *
     * @param string $configId
     * @param string $fileName
     * @throws InvalidConfigException
     */
    public function actionRestoreFromFile(string $configId, string $fileName = '')
    {
        $model = new Backup();
        $model->config_id = $configId;

        if (empty($fileName)) {
            $path     = Yii::$app->getModule('backup')->backupRootPath.DIRECTORY_SEPARATOR;
            $files    = scandir($path, SCANDIR_SORT_DESCENDING);
            $fileName = count($files) > 0 ? $files[0] : '';
        }

        if (empty($fileName)) {
            $this->stderr('Unable to find the backup file.' . PHP_EOL, Console::FG_RED);
        }

        $model->filename = $fileName;

        $this->stdout('Restoring from ' . $fileName . PHP_EOL, Console::FG_GREEN);
        Yii::createObject(BackupRestore::class, [$model])->run();
        $this->stdout('Backup restored.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * @param string $backup_id
     * @throws ErrorException
     * @throws StaleObjectException|Throwable
     */
    public function actionDelete(string $backup_id)
    {
        $model = Backup::findOne((int)$backup_id);
        if (!$model)
            throw new ErrorException('Backup not found.');

        $model->delete();
        $this->stdout('Backup deleted.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * List all created backups.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $models = Backup::find()->orderBy('id DESC')->all();
        
        if (empty($models)) {
            $this->stderr('Backups not found.' . PHP_EOL, Console::FG_YELLOW);
            return;
        }

        foreach ($models as $model)
            $this->stdout("{$model->id}: " . Yii::$app->formatter->asDatetime($model->date) . "\t\t{$model->config_id}\t\t" .
                BackupType::$list[$model->type] .
                PHP_EOL,
                $model->status ? Console::FG_GREEN : Console::FG_RED);
    }
}