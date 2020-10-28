<?php


namespace weareferal\matrixfieldpreview\assets\PreviewImage;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PreviewImageAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/previewimage/dist";

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/PreviewImage.js',
        ];

        $this->css = [
            'css/PreviewImage.css',
        ];

        parent::init();
    }
}
