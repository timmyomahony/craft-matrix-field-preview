<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;

use craft\web\Controller;


class SettingsController extends Controller
{
    public $defaultAction = 'index';
    protected $allowAnonymous = [];

    public function actionIndex()
    {
        $this->requireAdmin();
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        return $this->renderTemplate('matrix-field-preview/settings/index', [
            'settings' => $settings,
            'plugin' => $plugin
        ]);
    }
}
