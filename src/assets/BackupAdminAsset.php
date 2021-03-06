<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 21:28
 */

namespace yareg\backup\assets;


use yii\web\AssetBundle;

/**
 * Backup module admin asset bundle
 *
 * Class BackupAdminAsset
 * @package yareg\backup\assets
 */
class BackupAdminAsset extends AssetBundle
{
    public $publishOptions = [
        'forceCopy' => true,
    ];

    public $sourcePath = '@vendor/yareg-com/yii2-backup/src/assets/';

    public $css = [

    ];
    public $js = [
        'js/backup.admin.js'
    ];
    public $depends = [
        'floor12\notification\NotificationAsset',
        'yii\web\JqueryAsset'
    ];
}