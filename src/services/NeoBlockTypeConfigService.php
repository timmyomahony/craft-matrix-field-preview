<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\services\BaseBlockTypeConfigService;
use weareferal\matrixfieldpreview\records\NeoBlockTypeConfigRecord;

class NeoBlockTypeConfigService extends BaseBlockTypeConfigService
{
    protected $BlockTypeRecordClass = NeoBlockTypeConfigRecord::class;
}
