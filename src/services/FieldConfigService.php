<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\records\FieldConfigRecord;

use Craft;
use craft\base\Component;


abstract class BaseFieldConfigService extends Component
{
    protected $FieldRecordClass;
    protected $fieldType;
    /**
     * Get All
     * 
     * Get or create new field configs for every matrix field currently
     * saved in the system
     */
    public function getAll()
    {
        // TODO: performance can be improved here
        foreach ($this->getAllFields() as $field) {
            $record = $this->FieldRecordClass::findOne([
                'fieldId' => $field->id
            ]);

            if (!$record) {
                $fieldConfig = new $this->FieldRecordClass();
                $fieldConfig->fieldId = $field->id ?? null;
                $fieldConfig->siteId = Craft::$app->getSites()->currentSite->id;
                $fieldConfig->enablePreviews = true;
                $fieldConfig->enableTakeover = true;
                $fieldConfig->save();
            }
        }

        return $this->FieldRecordClass::find()->all();
    }

    /**
     * Get By Handle
     * 
     * Get a field config row based on it's associated matrix field handle
     */
    public function getByHandle($handle)
    {
        $field = Craft::$app->getFields()->getFieldByHandle($handle);

        if ($field) {
            $record = $this->FieldRecordClass::findOne([
                'fieldId' => $field->id
            ]);

            if (!$record) {
                $record = new $this->FieldRecordClass();
                $record->fieldId = $field->id ?? null;
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
     * Get all fields
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
    public function getAllFields()
    {
        $results = [];
        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            // @fixme: is this really the best way to get matrix fields?
            if (get_class($field) == $this->fieldType) {
                array_push($results, $field);
            }
        }
        return $results;
    }
}


class FieldConfigService extends BaseFieldConfigService
{
    protected $FieldRecordClass = FieldConfigRecord::class;
    protected $fieldType = 'craft\fields\Matrix';
}
