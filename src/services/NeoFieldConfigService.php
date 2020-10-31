<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\services\BaseFieldConfigService;
use weareferal\matrixfieldpreview\records\NeoFieldConfigRecord;


class NeoFieldConfigService extends BaseFieldConfigService
{
    protected $FieldRecordConfigClass = NeoFieldConfigRecord::class;
    protected $fieldType = 'benf\neo\Field';
}
