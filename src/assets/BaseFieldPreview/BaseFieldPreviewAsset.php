<?php

namespace weareferal\matrixfieldpreview\assets\BaseFieldPreview;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\matrix\MatrixAsset;

class BaseFieldPreviewAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/BaseFieldPreview/dist";

        $this->depends = [
            CpAsset::class,
            MatrixAsset::class
        ];

        $this->js = [
            'js/BaseFieldPreview.js',
            'js/BlockTypeInlinePreview.js',
            'js/BlockTypeModal.js',
            'js/BlockTypeModalButton.js',
        ];

        $this->css = [
            'css/BlockTypeInlinePreview.css',
            'css/BlockTypeModal.css',
            'css/BlockTypeModalButton.css',
        ];

        parent::init();
    }
}
