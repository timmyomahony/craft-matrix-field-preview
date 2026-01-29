<?php

namespace weareferal\matrixfieldpreview\assets\CardsFieldPreview;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

use weareferal\matrixfieldpreview\assets\BaseFieldPreview\BaseFieldPreviewAsset;

class CardsFieldPreviewAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/CardsFieldPreview/dist";

        $this->depends = [
            CpAsset::class,
            BaseFieldPreviewAsset::class
        ];

        $this->js = [
            'js/CardsFieldPreview.js',
        ];

        $this->css = [
            'css/CardsFieldPreview.css',
        ];

        parent::init();
    }
}
