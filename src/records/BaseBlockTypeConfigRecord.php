<?php

namespace weareferal\matrixfieldpreview\records;

use yii\db\ActiveQueryInterface;


use Craft;
use craft\db\ActiveRecord;
use craft\records\Asset;

use craft\records\MatrixBlockType;


abstract class BaseBlockTypeConfigRecord extends ActiveRecord
{
    protected $BlockTypeClass;

    public function getField(): ActiveQueryInterface
    {
        return $this->hasOne(Field::class, ['id' => 'fieldId']);
    }

    public function setField($field)
    {
        $this->fieldId = $field->id ?? null;
    }

    /**
     * Get block type
     * 
     * An active record foreign key accessor
     * 
     * @fixme: why does Craft not use setters in any of its Records?
     */
    public function getBlockType(): ActiveQueryInterface
    {
        return $this->hasOne($this->BlockTypeClass, ['id' => 'blockTypeId']);
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

        return Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/MatrixFieldPreviewSettings/dist/img/dummy-image.png', true);
    }

    public function rules()
    {
        return [
            ['description', 'string', 'max' => 1024],
        ];
    }
}