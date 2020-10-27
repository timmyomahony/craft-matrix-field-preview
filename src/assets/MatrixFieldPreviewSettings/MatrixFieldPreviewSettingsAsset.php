<?php


namespace weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class MatrixFieldPreviewSettingsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@weareferal/matrixfieldpreview/assets/matrixfieldpreviewsettings/dist";

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/MatrixFieldPreviewSettings.js',
        ];

        $this->css = [
            'css/MatrixFieldPreviewSettings.css',
        ];

        parent::init();
    }
}
