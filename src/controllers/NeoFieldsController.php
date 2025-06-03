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

    // Unlike the Matrix Field equivilant for this page, we are able to
    // save a global setting neoDisableForSingleChilden for Neo Fields
    public function actionSave()
    {
        $this->requirePostRequest();
        
        $plugin = MatrixFieldPreview::getInstance();
        $post = $this->request->post();

        Craft::$app->getPlugins()->savePluginSettings($plugin, [
            "neoDisableForSingleChilden" => $post['neoDisableForSingleChilden'],
        ]);

        $this->setSuccessFlash(Craft::t('matrix-field-preview', 'Field settings saved'));
        return $this->redirectToPostedUrl();
    }

}
