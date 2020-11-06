<?php

namespace weareferal\matrixfieldpreview\assets\NeoFieldPreview;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class NeoFieldPreviewAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/NeoFieldPreview/dist";

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/NeoFieldPreview.js',
        ];

        parent::init();
    }
}
