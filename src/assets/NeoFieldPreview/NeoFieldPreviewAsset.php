<?php

namespace weareferal\matrixfieldpreview\assets\NeoFieldPreview;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreview\MatrixFieldPreviewAsset;

class NeoFieldPreviewAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/NeoFieldPreview/dist";

        $this->depends = [
            CpAsset::class,
            MatrixFieldPreviewAsset::class
        ];

        $this->js = [
            'js/NeoFieldPreview.js',
        ];

        parent::init();
    }
}
