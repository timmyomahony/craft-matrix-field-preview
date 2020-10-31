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
        $matrixFields = $plugin->blockTypeConfigService->getAllMatrixFields();

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

    /**
     * Get all matrix fields
     * 
     * There is already a method in the fields service to get all fields
     * by a particular element type:
     * 
     * https://docs.craftcms.com/api/v3/craft-services-fields.html#public-methods
     * 
     * but this method is misleading as there is no matrix element type, just
     * a matrix _block_ element types. So you can only use it to search for matrix
     * blocks by type, not actual matrix fields themselves. 
     * 
     * So instead, we have our own function here
     */
    public function getAllMatrixFields()
    {
        $results = [];
        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            // @fixme: is this really the best way to get matrix fields?
            if (get_class($field) == 'craft\fields\Matrix') {
                array_push($results, $field);
            }
        }
        return $results;
    }
}
