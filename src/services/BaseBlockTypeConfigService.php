<?php

namespace weareferal\matrixfieldpreview\services;

use Craft;
use craft\base\Component;


abstract class BaseBlockTypeConfigService extends Component
{
    protected $BlockTypeRecordConfigClass;

    /**
     * Get All
     * 
     * Get all block type config rows
     */
    public function getAll()
    {
        return $this->BlockTypeRecordConfigClass::find()->all();
    }

    /**
     * Get By ID
     * 
     * Get an individual block type *config* by its ID
     */
    public function getById($id)
    {
        $record = $this->BlockTypeRecordConfigClass::findOne([
            'id' => $id
        ]);

        if (!$record) {
            return null;
        }

        return $record;
    }

    /**
     * Get By Block Type ID
     * 
     * Get all block type *config* rows from their related block type ID
     */
    public function getOrCreateByBlockTypeId($blockTypeId, $create = true)
    {
        $record = $this->BlockTypeRecordConfigClass::findOne([
            'blockTypeId' => $blockTypeId
        ]);

        if (!$record) {
            if ($create) {
                $blockType = $this->getBlockTypeById($blockTypeId);
                $record = new $this->BlockTypeRecordConfigClass();
                $record->description = "";
                $record->fieldId = $blockType->field->id;
                $record->blockTypeId = $blockType->id;
                $record->save();
            } else {
                return null;
            }
        }

        return $record;
    }

    /**
     * Get Or Create By Field Handle
     * 
     * Get all block type *config* rows from their related field handle and
     * create them if they don't already exist
     */
    public function getOrCreateByFieldHandle($handle)
    {
        $field = Craft::$app->getFields()->getFieldByHandle($handle);

        $blockTypes = $this->getBlockTypeByFieldHandle($field->handle);

        $records = [];
        foreach ($blockTypes as $blockType) {

            $record = $this->BlockTypeRecordConfigClass::findOne([
                'blockTypeId' => $blockType->id
            ]);

            if ($record == null) {
                $record = new $this->BlockTypeRecordConfigClass();
                $record->description = "";
                $record->fieldId = $field->id;
                $record->blockTypeId = $blockType->id;
                $record->save();
            }

            array_push($records, $record);
        }

        return $records;
    }

    /**
     * Get Block Type by ID
     * 
     * Get a block type (not config) by ID
     */
    public function getBlockTypeById($blockTypeId)
    {
        throw new \BadMethodCallException("Method not implemented");
    }

    /**
     * Get Block Type by Field Handle
     * 
     * Get a block type (not config) by ID
     */
    public function getBlockTypeByFieldHandle($handle)
    {
        throw new \BadMethodCallException("Method not implemented");
    }
}