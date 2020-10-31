<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\services\BaseFieldConfigService;
use weareferal\matrixfieldpreview\records\FieldConfigRecord;


class MatrixFieldConfigService extends BaseFieldConfigService
{
    protected $FieldRecordClass = FieldConfigRecord::class;
    protected $fieldType = 'craft\fields\Matrix';
}
