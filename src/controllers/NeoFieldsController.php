<?php

namespace weareferal\matrixfieldpreview\controllers;

use Craft;
use weareferal\matrixfieldpreview\controllers\BaseFieldsController;
use weareferal\matrixfieldpreview\MatrixFieldPreview;

class NeoFieldsController extends BaseFieldsController
{

    protected function getIndexTemplate() {
        return 'matrix-field-preview/settings/neo-fields/index';
    }

    protected function getEditTemplate() {
        return 'matrix-field-preview/settings/neo-fields/_edit';
    }

    protected function getService($plugin) {
        return $plugin->neoFieldConfigService;
    }

    protected function getSuccessMessage() {
        return Craft::t('matrix-field-preview', 'Neo field configuration saved.');
    }

    protected function getEditAction($id)
    {
        return 'matrix-field-preview/settings/neo-fields/' . $id;
    }

    // public function actionSave()
    // {
    //     $plugin = MatrixFieldPreview::getInstance();
    //     $post = $this->request->post();

    //     Craft::$app->getPlugins()->savePluginSettings($plugin, [
    //         "neoDisableForSingleChilden" => $post['neoDisableForSingleChilden'],
    //     ]);

    //     return parent::actionSave();
    // }

}
