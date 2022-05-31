<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;

use Craft;
use craft\web\Controller;

abstract class BaseFieldsController extends Controller {
    protected $allowAnonymous = [];

    protected function _actionFields($fieldService, $template, $templateVars = [])
    {
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        $request = Craft::$app->request;

        if ($request->isPost) {
            $post = $request->post();
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
        }

        $fields = $fieldService->getAllFields();
        $fieldConfigs = $fieldService->getAll();

        usort($fieldConfigs, function ($a, $b) {
            return strcmp($a->field->name, $b->field->name);
        });

        return $this->renderTemplate($template, array_merge($templateVars, [
            'settings' => $settings,
            'plugin' => $plugin,
            'fields' => $fields,
            'fieldConfigs' => $fieldConfigs
        ]));
    }
}