<?php

namespace weareferal\matrixfieldpreview\records;

use yii\db\ActiveQueryInterface;
use craft\db\ActiveRecord;
use craft\records\Field;


class NeoFieldConfigRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%matrixfieldpreview_neo_fields_config}}';
    }

    /**
     * Get block type
     * 
     * An active record foreign key accessor
     * 
     * @fixme: why does Craft not use setters in any of its Records?
     */
    public function getField(): ActiveQueryInterface
    {
        return $this->hasOne(Field::class, ['id' => 'fieldId']);
    }
}
