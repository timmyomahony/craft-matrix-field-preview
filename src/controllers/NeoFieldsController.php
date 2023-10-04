<?php

namespace weareferal\matrixfieldpreview\controllers;

use Craft;
use weareferal\matrixfieldpreview\controllers\BaseFieldsController;
use weareferal\matrixfieldpreview\MatrixFieldPreview;

class NeoFieldsController extends BaseFieldsController
{
    public function actionSave()
    {
        $plugin = MatrixFieldPreview::getInstance();
        $post = $this->request->post();

        Craft::$app->getPlugins()->savePluginSettings($plugin, [
            "neoDisableForSingleChilden" => $post['neoDisableForSingleChilden'],
        ]);

        return parent::actionSave();
    }

    protected function getTemplate()
    {
        return 'matrix-field-preview/settings/neo-fields/index';
    }

    protected function getService($plugin)
    {
        return $plugin->neoFieldConfigService;
    }

    protected function getSuccessMessage()
    {
        return Craft::t('matrix-field-preview', 'Neo field configurations saved.');
    }
}
