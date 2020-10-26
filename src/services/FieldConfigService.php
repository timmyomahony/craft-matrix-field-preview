<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\records\FieldConfigRecord;

use Craft;
use craft\base\Component;
use craft\fields\Matrix;
use craft\helpers\Assets as AssetsHelper;
use craft\elements\Asset;
use craft\errors\VolumeException;
use craft\helpers\Image;
use craft\errors\ImageException;
use craft\errors\InvalidSubpathException;

/**
 * Field Config Servilce
 *
 * @author    Timmy O'Mahony 
 * @package   MatrixFieldPreview
 * @since     1.2.0
 */
class FieldConfigService extends Component
{
    /**
     * Get All
     * 
     * Get or create new field configs for every matrix field currently
     * saved in the system
     */
    public function getAll()
    {
        $plugin = MatrixFieldPreview::getInstance();
        $matrixFields = $plugin->previewService->getAllMatrixFields();

        // TODO: performance can be improved here
        foreach ($matrixFields as $matrixField) {
            $record = FieldConfigRecord::findOne([
                'fieldId' => $matrixField->id
            ]);

            if (!$record) {
                $fieldConfig = new FieldConfigRecord();
                $fieldConfig->fieldId = $matrixField->id ?? null;
                $fieldConfig->siteId = Craft::$app->getSites()->currentSite->id;
                $fieldConfig->enablePreviews = true;
                $fieldConfig->enableTakeover = true;
                $fieldConfig->save();
            }
        }

        return FieldConfigRecord::find()->all();
    }

    /**
     * Get By Handle
     * 
     * Get a field config row based on it's associated matrix field handle
     */
    public function getByHandle($handle)
    {
        $matrixField = Craft::$app->getFields()->getFieldByHandle($handle);

        if ($matrixField) {
            $record = FieldConfigRecord::findOne([
                'fieldId' => $matrixField->id
            ]);

            if (!$record) {
                $record = new FieldConfigRecord();
                $record->fieldId = $matrixField->id ?? null;
                $record->siteId = Craft::$app->getSites()->currentSite->id;
                $record->enablePreviews = true;
                $record->enableTakeover = true;
                $record->save();
            }

            return $record;
        }

        return null;
    }
}
