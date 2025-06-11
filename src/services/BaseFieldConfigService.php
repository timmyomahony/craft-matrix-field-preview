<?php

namespace weareferal\matrixfieldpreview\services;

use Craft;
use craft\base\Component;


abstract class BaseFieldConfigService extends Component
{
    protected $FieldRecordConfigClass;
    protected $fieldType;

    /**
     * Get All
     * 
     * Get or create new field configs for every matrix field currently
     * saved in the system
     */
    public function getAll()
    {
        // Create any missing field configs
        //
        // TODO: performance can be improved here
        foreach ($this->getAllFields() as $field) {
            $record = $this->FieldRecordConfigClass::findOne([
                'fieldId' => $field->id
            ]);

            if (!$record) {
                $fieldConfig = new $this->FieldRecordConfigClass();
                $fieldConfig->fieldId = $field->id ?? null;
                $fieldConfig->enablePreviews = true;
                $fieldConfig->enableTakeover = true;
                $fieldConfig->save();
            }
        }

        // Get all configs and filter out those where the field has been soft deleted
        $fieldConfigs = $this->FieldRecordConfigClass::find()
            ->joinWith(['field'])
            ->where(['fields.dateDeleted' => null])
            ->all();

        return $fieldConfigs;
    }

    /**
     * Get or create a MFP Field Config given the underlying field handle
     * 
     */
    public function getOrCreateByFieldHandle($handle)
    {
        $field = Craft::$app->getFields()->getFieldByHandle($handle);


        if ($field) {
            $record = $this->FieldRecordConfigClass::findOne([
                'fieldId' => $field->id
            ]);

            if ($record == null) {
                $record = new $this->FieldRecordConfigClass();
                $record->fieldId = $field->id ?? null;
                $record->enablePreviews = true;
                $record->enableTakeover = true;
                $record->save();
            }

            return $record;
        }

        return null;
    }

    /**
     * Get or create a MFP Field Config given the underlying field ID
     * 
     */
    public function getOrCreateByFieldId($fieldId)
    {
        $field = Craft::$app->getFields()->getFieldById($fieldId);

        if ($field) {
            $record = $this->FieldRecordConfigClass::findOne([
                'fieldId' => $field->id
            ]);

            if ($record == null) {
                $record = new $this->FieldRecordConfigClass();
                $record->fieldId = $field->id ?? null;
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
        $fields = [];
        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            // @fixme: is this really the best way to get matrix fields?
            if (get_class($field) == $this->fieldType && $field->dateDeleted == null) {
                array_push($fields, $field);
            }
        }
        
        return $fields;
    }

    public function save($fieldConfig): bool {
        if (! $fieldConfig->validate()) {
            Craft::info("Field config not saved due to validation error", "matrix-field-preview");
            return false;
        }

        $fieldConfig->save();

        return true;
    }
}
