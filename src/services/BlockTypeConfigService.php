<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\records\BlockTypeConfigRecord;

use Craft;
use craft\base\Component;


/**
 *
 */
abstract class BaseBlockTypeConfigService extends Component
{
    protected $BlockTypeRecordClass;

    /**
     * 
     */
    public function getAll()
    {
        return $this->BlockTypeRecordClass::find()->all();
    }

    /**
     * 
     */
    public function getByBlockTypeId($blockTypeId)
    {
        $record = $this->BlockTypeRecordClass::findOne([
            'blockTypeId' => $blockTypeId
        ]);

        if (!$record) {
            return null;
        }

        return $record;
    }

    /**
     * 
     */
    public function getByHandle($handle)
    {
        $matrixField = Craft::$app->getFields()->getFieldByHandle($handle);

        if ($matrixField) {
            $records = $this->BlockTypeRecordClass::find()->where([
                'fieldId' => $matrixField->id
            ])->all();

            if (!$records) {
                return [];
            }

            return $records;
        }

        return [];
    }

    /**
     * 
     */
    public function getById($id)
    {
        $record = $this->BlockTypeRecordClass::findOne([
            'id' => $id
        ]);

        if (!$record) {
            return null;
        }

        return $record;
    }
}


/**
 * 
 */
class BlockTypeConfigService extends BaseBlockTypeConfigService
{
    protected $BlockTypeRecordClass = BlockTypeConfigRecord::class;
}
