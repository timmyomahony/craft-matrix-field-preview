<?php


namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\previewsettings\PreviewSettingsAsset;

use Craft;
use craft\web\Controller;


class SettingsController extends Controller
{

    protected $allowAnonymous = [];

    public function actionFields()
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('matrix-field-preview/settings/fields', [
            'settings' => $settings,
            'plugin' => $plugin
        ]);
    }

    public function actionPreviews()
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $view = Craft::$app->getView();
        $view->registerAssetBundle(PreviewSettingsAsset::class);

        $assets = [
            'success' => Craft::$app->getAssetManager()->getPublishedUrl('@app/web/assets/cp/dist', true, 'images/success.png')
        ];

        $blockTypes = Craft::$app->matrix->getAllBlockTypes();
        $previews = $plugin->previewService->getAll();

        $previewsMap = [];
        foreach ($previews as $preview) {
            $previewsMap[$preview->blockType->id] = $preview;
        }

        $matrixFieldsMap = [];
        foreach ($blockTypes as $blockType) {
            $matrixField = $blockType->field;

            // Initialise an array for each matrix field
            if (!array_key_exists($matrixField->id, $matrixFieldsMap)) {
                $matrixFieldsMap[$matrixField->id] = [
                    'matrixField' => $matrixField,
                    'rows' => []
                ];
            }

            // Get the preview for this block type if it exists
            $preview = null;
            if (array_key_exists($blockType->id, $previewsMap)) {
                $preview = $previewsMap[$blockType->id];
            }

            array_push($matrixFieldsMap[$matrixField->id]['rows'], [
                'blockType' => $blockType,
                'preview' => $preview
            ]);
        }

        $matrixFields = [];
        foreach ($matrixFieldsMap as $key => $value) {
            array_push($matrixFields, $value);
        }

        return $this->renderTemplate('matrix-field-preview/settings/previews', [
            'settings' => $settings,
            'plugin' => $plugin,
            'assets' => $assets,
            'matrixFields' => $matrixFields
        ]);
    }
}
