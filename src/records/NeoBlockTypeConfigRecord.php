<?php

namespace weareferal\matrixfieldpreview\records;

use weareferal\matrixfieldpreview\records\BaseBlockTypeConfigRecord;
use neo\records\BlockType;


class NeoBlockTypeConfigRecord extends BaseBlockTypeConfigRecord
{
    protected $BlockTypeClass = BlockType::class;

    public static function tableName()
    {
        return '{{%matrixfieldpreview_neo_blocktypes_config}}';
    }
}
