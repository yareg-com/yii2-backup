<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 11.11.2019
 * Time: 07:45
 */

namespace yareg\backup\tests\unit;

/**
 * This is a tests for Backup class
 */

use yareg\backup\models\Backup;
use yareg\backup\tests\TestCase;
use Yii;

class BackupTest extends TestCase
{
    public function testGetFullPathWithEmptyFilename()
    {
        $model = new Backup();
        $this->assertNull($model->filename);
        $this->assertNull($model->getFullPath());
    }

    public function testGetFullPathWithFilename()
    {
        $model = new Backup();
        $model->filename = rand(0, 999);
        $path = Yii::getAlias($this->module->backupFolder) . DIRECTORY_SEPARATOR . $model->filename;
        $this->assertEquals($path, $model->getFullPath());
    }

    public function testUpdateFileSizeWithNoFile()
    {
        $model = new Backup();
        $this->assertNull($model->filename);
        $model->updateFileSize();
        $this->assertEquals(0, $model->size);
    }

    public function testUpdateFileSizeWithFile()
    {
        $fileToCheckSize = 'sqlite.db';
        $fileToCheckSizePath = Yii::getAlias($this->module->backupFolder) . DIRECTORY_SEPARATOR . $fileToCheckSize;
        $model = new Backup();
        $model->filename = $fileToCheckSize;
        $model->updateFileSize();
        $this->assertEquals(filesize($fileToCheckSizePath), $model->size);
    }

    public function testUnlinkFileAfterDelete()
    {
        $fileToCheckSize = 'test_backup.tgz';
        $fileToCheckSizePath = Yii::getAlias($this->module->backupFolder) . DIRECTORY_SEPARATOR . $fileToCheckSize;
        file_put_contents($fileToCheckSizePath, 'this is a test file content');
        $model = new Backup();
        $model->filename = $fileToCheckSize;
        $this->assertFileExists($fileToCheckSizePath);
        $model->delete();
        $this->assertFileNotExists($fileToCheckSizePath);
    }


}