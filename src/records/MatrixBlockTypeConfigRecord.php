<?php

namespace weareferal\matrixfieldpreview\records;

use craft\records\MatrixBlockType;

use weareferal\matrixfieldpreview\records\BaseBlockTypeConfigRecord;


class MatrixBlockTypeConfigRecord extends BaseBlockTypeConfigRecord
{
    protected $BlockTypeClass = MatrixBlockType::class;

    public static function tableName()
    {
        return '{{%matrixfieldpreview_blocktypes_config}}';
    }
}
