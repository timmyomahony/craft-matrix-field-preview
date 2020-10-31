<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\services\BaseFieldConfigService;
use weareferal\matrixfieldpreview\records\NeoFieldConfigRecord;


class NeoFieldConfigService extends BaseFieldConfigService
{
    protected $FieldRecordClass = NeoFieldConfigRecord::class;
    protected $fieldType = 'craft\fields\Neo';
}
