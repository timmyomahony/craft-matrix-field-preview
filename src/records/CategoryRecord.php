<?php

namespace weareferal\matrixfieldpreview\records;

use yii\db\ActiveQueryInterface;
use craft\db\ActiveRecord;
use craft\records\Field;


class CategoryRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%matrixfieldpreview_category}}';
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100],
            ['description', 'string', 'max' => 1000],
        ];
    }
}
