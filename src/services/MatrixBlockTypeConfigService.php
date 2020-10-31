<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\records\MatrixBlockTypeConfigRecord;
use weareferal\matrixfieldpreview\services\BaseBlockTypeConfigService;
use Craft;
use craft\base\Component;

/**
 * 
 */
class MatrixBlockTypeConfigService extends BaseBlockTypeConfigService
{
    protected $BlockTypeRecordConfigClass = MatrixBlockTypeConfigRecord::class;

    protected function getBlockTypeById($blockTypeId)
    {
        return Craft::$app->matrix->getBlockTypeById($blockTypeId);
    }

    protected function getBlockTypeByFieldHandle($handle)
    {
        $field = Craft::$app->getFields()->getFieldByHandle($handle);
        if ($field) {
            return Craft::$app->matrix->getBlockTypesByFieldId($field->id);
        }
        return null;
    }
}
