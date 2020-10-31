<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\services\BaseFieldConfigService;
use weareferal\matrixfieldpreview\records\MatrixFieldConfigRecord;


class MatrixFieldConfigService extends BaseFieldConfigService
{
    protected $FieldRecordClass = MatrixFieldConfigRecord::class;
    protected $fieldType = 'craft\fields\Matrix';
}
