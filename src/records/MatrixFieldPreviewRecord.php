<?php
/**
 * Matrix Field Preview plugin for Craft CMS 3.x
 *
 * Gives you the ability to configure a preview for all your matrix field blocks, giving your clients a better publishing experience.
 *
 * @link      https://weareferal.com
 * @copyright Copyright (c) 2020 Timmy O'Mahony 
 */

namespace weareferal\matrixfieldpreview\records;

use weareferal\matrixfieldpreview\MatrixFieldPreview;

use Craft;
use craft\db\ActiveRecord;
use craft\records\MatrixBlockType;

/**
 * MatrixFieldPreviewRecord Record
 *
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * Active Record implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
 * The premise behind Active Record is that an individual [[ActiveRecord]] object is associated with a specific
 * row in a database table. The object's attributes are mapped to the columns of the corresponding table.
 * Referencing an Active Record attribute is equivalent to accessing the corresponding table column for that record.
 *
 * http://www.yiiframework.com/doc-2.0/guide-db-active-record.html
 *
 * @author    Timmy O'Mahony 
 * @package   MatrixFieldPreview
 * @since     1.0.0
 */
class MatrixFieldPreviewRecord extends ActiveRecord
{
    // public $blockTypeId;
    // public $description;

    public static function tableName()
    {
        return '{{%matrixfieldpreview_matrixfieldpreviewrecord}}';
    }

    public function getBlockType()
    {
        return $this->hasOne(MatrixBlockType::className(), ['id' => 'blockTypeId']);
    }

    // public function attributeLabels()
    // {
    //     return [
    //         'description' => 'Preview description',
    //         'blockTypeId' => 'Matrix block type',
    //     ];
    // }

    // public function rules()
    // {
    //     return [
    //         ['description', 'string'],
    //         [['description', 'blockTypeId'], 'required']
    //     ];
    // }
}
