<?php

namespace weareferal\matrixfieldpreview\records;

use yii\db\ActiveQueryInterface;

use Craft;
use craft\db\ActiveRecord;
use craft\records\Asset;

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
