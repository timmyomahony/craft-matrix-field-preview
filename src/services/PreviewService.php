<?php

/**
 * Matrix Field Preview plugin for Craft CMS 3.x
 *
 * Gives you the ability to configure a preview for all your matrix field blocks, giving your clients a better publishing experience.
 *
 * @link      https://weareferal.com
 * @copyright Copyright (c) 2020 Timmy O'Mahony 
 */

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\records\PreviewRecord;
// use weareferal\matrixfieldpreview\models\MatrixFieldPreviewModel;

use Craft;
use craft\base\Component;
use craft\helpers\Assets as AssetsHelper;
use craft\elements\Asset;
use craft\errors\VolumeException;
use craft\helpers\Image;
use craft\errors\ImageException;
use craft\errors\InvalidSubpathException;

/**
 * PreviewService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Timmy O'Mahony 
 * @package   MatrixFieldPreview
 * @since     1.0.0
 */
class PreviewService extends Component
{
    public function getAll()
    {
        return PreviewRecord::find()->all();
    }

    public function getByBlockTypeId($blockTypeId)
    {
        $record = PreviewRecord::findOne([
            'blockTypeId' => $blockTypeId
        ]);

        if (!$record) {
            return null;
        }

        return $record;
    }

    public function getByHandle($handle)
    {
        $records = PreviewRecord::find([
            'matrixFieldHandle' => $handle
        ])->all();

        if (!$records) {
            return [];
        }

        return $records;
    }

    public function getById($id)
    {
        $record = PreviewRecord::findOne([
            'id' => $id
        ]);

        if (!$record) {
            return null;
        }

        return $record;
    }
}
