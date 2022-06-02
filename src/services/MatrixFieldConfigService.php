<?php

namespace weareferal\matrixfieldpreview\services;

use Craft;
use craft\base\Component;

use weareferal\matrixfieldpreview\records\MatrixFieldConfigRecord;
use weareferal\matrixfieldpreview\services\BaseFieldConfigService;


class MatrixFieldConfigService extends BaseFieldConfigService
{
    protected $FieldRecordConfigClass = MatrixFieldConfigRecord::class;
    protected $fieldType = 'craft\fields\Matrix';
}
