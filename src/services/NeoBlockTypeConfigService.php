<?php

namespace weareferal\matrixfieldpreview\services;

use Craft;
use weareferal\matrixfieldpreview\services\BaseBlockTypeConfigService;
use weareferal\matrixfieldpreview\records\NeoBlockTypeConfigRecord;

use benf\neo\Plugin as Neo;

class NeoBlockTypeConfigService extends BaseBlockTypeConfigService
{
    protected $BlockTypeRecordConfigClass = NeoBlockTypeConfigRecord::class;

    protected function getBlockTypeById($blockTypeId)
    {
        return Neo::getInstance()->blockTypes->getById($blockTypeId);
    }

    protected function getBlockTypeByFieldHandle($handle)
    {
        $field = Craft::$app->getFields()->getFieldByHandle($handle);
        if ($field) {
            return Neo::getInstance()->blockTypes->getByFieldId($field->id);
        }
        return null;
    }
}
