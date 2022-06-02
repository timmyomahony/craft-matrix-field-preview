<?php

namespace weareferal\matrixfieldpreview\records;

use benf\neo\records\BlockType;

use weareferal\matrixfieldpreview\records\BaseBlockTypeConfigRecord;


class NeoBlockTypeConfigRecord extends BaseBlockTypeConfigRecord
{
    protected $BlockTypeClass = BlockType::class;

    public static function tableName()
    {
        return '{{%matrixfieldpreview_neo_blocktypes_config}}';
    }
}
