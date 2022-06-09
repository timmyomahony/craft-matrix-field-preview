<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;

use craft\web\Controller;


class SettingsController extends Controller
{
    public $defaultAction = 'index';
    protected array|bool|int $allowAnonymous = [];

    /**
     * Enforce admin privileges
     * 
     * But ignore the settings `allowAdminChanges`, allowing users to
     * configure the plugin while on production.
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin($requireAdminChanges=false);
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        return $this->renderTemplate('matrix-field-preview/settings/index', [
            'settings' => $settings,
            'plugin' => $plugin
        ]);
    }
}
