<?php

namespace weareferal\matrixfieldpreview\assets\MatrixFieldPreview;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class MatrixFieldPreviewAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/MatrixFieldPreview/dist";

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/BlockTypeInlinePreview.js',
            'js/BlockTypeGrid.js',
            'js/BlockTypeModal.js',
            'js/BlockTypeModalButton.js',
            'js/MatrixFieldPreview.js',
        ];

        $this->css = [
            'css/BlockTypeInlinePreview.css',
            'css/BlockTypeGrid.css',
            'css/BlockTypeModal.css',
            'css/BlockTypeModalButton.css',
            'css/MatrixFieldPreview.css',
        ];

        parent::init();
    }
}
