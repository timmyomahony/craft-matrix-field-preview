<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;

use Craft;
use craft\web\Controller;


class MatrixFieldsController extends Controller
{
    public $defaultAction = 'index';

    public function beforeAction($action): bool
    {
        $this->requireAdmin();
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);
        $plugin = MatrixFieldPreview::getInstance();       
        $fieldService = $plugin->matrixFieldConfigService;

        $fields = $fieldService->getAllFields();
        $fieldConfigs = $fieldService->getAll($sort = true);

        return $this->renderTemplate('matrix-field-preview/settings/matrix-fields/index', [
            'fields' => $fields,
            'fieldConfigs' => $fieldConfigs
        ]);
    }

    public function actionSave()
    {
        $this->requirePostRequest();
        $plugin = MatrixFieldPreview::getInstance();
        $fieldService = $plugin->matrixFieldConfigService;

        $post = $this->request->post();
        foreach ($post['settings'] as $handle => $values) {
            $fieldConfig = $fieldService->getOrCreateByFieldHandle($handle);
            if ($fieldConfig) {
                $fieldConfig->enablePreviews = $values['enablePreviews'];
                $fieldConfig->enableTakeover = $values['enableTakeover'];
                if ($fieldConfig->validate()) {
                    $fieldConfig->save();
                }
            }
        }

        $fields = $fieldService->getAllFields();
        $fieldConfigs = $fieldService->getAll($sort = true);

        $this->setSuccessFlash(Craft::t('matrix-field-preview', 'Matrix field configurations saved.'));
        return $this->redirectToPostedUrl();
    }
}
