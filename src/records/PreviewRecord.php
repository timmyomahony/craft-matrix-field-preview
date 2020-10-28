<?php

namespace weareferal\matrixfieldpreview\records;

use Craft;
use craft\db\ActiveRecord;
use craft\records\MatrixBlockType;
use craft\records\Asset;
use craft\services\Elements;

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
            // Make sure the asset hasn't been soft deleted
            // https://github.com/weareferal/craft-matrix-field-preview/issues/36
            if ($element && !$element->trashed) {
                return Craft::$app->getAssets()->getThumbUrl($element, $width, false);
            }
        }

        return Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/previewimage/dist/img/dummy-image.svg', true);
    }
}
