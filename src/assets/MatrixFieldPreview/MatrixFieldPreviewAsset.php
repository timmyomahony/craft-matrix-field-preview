<?php

namespace weareferal\matrixfieldpreview\assets\MatrixFieldPreview;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

use weareferal\matrixfieldpreview\assets\BaseFieldPreview\BaseFieldPreviewAsset;

class MatrixFieldPreviewAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/MatrixFieldPreview/dist";

        $this->depends = [
            CpAsset::class,
            BaseFieldPreviewAsset::class
        ];

        $this->js = [
            'js/MatrixFieldPreview.js',
        ];

        $this->css = [
            'css/MatrixFieldPreview.css',
        ];

        parent::init();
    }
}
