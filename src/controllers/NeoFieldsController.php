<?php

namespace weareferal\matrixfieldpreview\controllers;

use Craft;
use weareferal\matrixfieldpreview\controllers\BaseFieldsController;
use weareferal\matrixfieldpreview\MatrixFieldPreview;

class NeoFieldsController extends BaseFieldsController
{
    // public function actionSave()
    // {
    //     $plugin = MatrixFieldPreview::getInstance();
    //     $post = $this->request->post();

    //     Craft::$app->getPlugins()->savePluginSettings($plugin, [
    //         "neoDisableForSingleChilden" => $post['neoDisableForSingleChilden'],
    //     ]);

    //     return parent::actionSave();
    // }

    protected function getIndexTemplate()
    {
        return 'matrix-field-preview/settings/neo-fields/index';
    }

    protected function getEditTemplate()
    {
        return 'matrix-field-preview/settings/neo-fields/_edit';
    }

    protected function getService($plugin)
    {
        return $plugin->neoFieldConfigService;
    }

    protected function getSuccessMessage()
    {
        return Craft::t('matrix-field-preview', 'Neo field configuration saved.');
    }
}
