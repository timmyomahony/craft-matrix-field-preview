<?php

namespace weareferal\matrixfieldpreview\assets\PreviewField;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PreviewFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/previewfield/dist";

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/PreviewField.js',
        ];

        $this->css = [
            'css/PreviewField.css',
        ];

        parent::init();
    }
}
