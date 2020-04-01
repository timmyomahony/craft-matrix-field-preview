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

use Craft;
use craft\db\ActiveRecord;
use craft\records\MatrixBlockType;
use craft\records\Asset;
use craft\services\Elements;

/**
 * PreviewRecord Record
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
class PreviewRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%matrixfieldpreview_previewrecord}}';
    }

    /**
     * Get block type
     * 
     * An active record foreign key accessor
     */
    public function getBlockType()
    {
        return $this->hasOne(MatrixBlockType::className(), ['id' => 'blockTypeId']);
    }

    public function setBlockType($blockType)
    {
        $this->blockTypeId = $blockType->id ?? null;
    }

    /**
     * Get preview image (asset)
     * 
     * An active record foreign key acessor
     * 
     * FIXME: should this return an Element instead of an Record?
     */
    public function getPreviewImage()
    {
        return $this->hasOne(Asset::className(), ['id' => 'previewImageId']);
    }

    /**
     * Set the preview image (asset)
     */
    public function setPreviewImage($previewImage)
    {
        $this->previewImageId = $previewImage->id ?? null;
    }

    /**
     * Get a thumbnail of the preview image
     * 
     * FIXME: this is just proxying the request to the Asset Element which
     * has its own getThumbUrl. Why not just return an Element from the above
     * getPreviewImage so we could use that {{ preview.previewImage.getThumbUrl() }}
     */
    public function getThumbUrl(int $width, int $height = null)
    {
        if ($this->previewImage) {
            $element = Craft::$app->getElements()->getElementById($this->previewImage->id);
            return Craft::$app->getAssets()->getThumbUrl($element, $width, $height, false);
        }

        return Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/PreviewImage/dist/img/dummy-image.svg', true);
    }
}
