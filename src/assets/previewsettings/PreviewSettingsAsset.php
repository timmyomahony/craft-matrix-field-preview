<?php


namespace weareferal\matrixfieldpreview\assets\PreviewSettings;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PreviewSettingsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/previewsettings/dist";

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [];

        $this->css = [
            'css/PreviewSettings.css',
        ];

        parent::init();
    }
}
