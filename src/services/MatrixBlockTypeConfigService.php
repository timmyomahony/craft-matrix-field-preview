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
    protected $BlockTypeRecordClass = MatrixBlockTypeConfigRecord::class;
}
