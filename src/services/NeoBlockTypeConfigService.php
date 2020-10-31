<?php

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\services\BaseBlockTypeConfigService;
use weareferal\matrixfieldpreview\records\NeoBlockTypeConfigRecord;

class BlockTypeConfigService extends BaseBlockTypeConfigService
{
    protected $recordClass = NeoBlockTypeConfigRecord::class;
}
