<?php


namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;
use weareferal\matrixfieldpreview\records\BlockTypeConfigRecord;

use Craft;
use craft\web\Controller;
use craft\web\UploadedFile;
use craft\errors\UploadFailedException;
use craft\helpers\Assets;
use craft\helpers\FileHelper;
use craft\elements\Asset;


class SettingsController extends Controller
{

    protected $allowAnonymous = [];

    /**
     * General Plugin Settings
     */
    public function actionGeneral()
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        return $this->renderTemplate('matrix-field-preview/settings/general', [
            'settings' => $settings,
            'plugin' => $plugin
        ]);
    }

    /**
     * Matrix Fields Settings
     * 
     */
    public function actionFields()
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        $request = Craft::$app->request;

        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        if ($request->isPost) {
            $post = $request->post();
            foreach ($post['settings'] as $handle => $values) {
                $fieldConfig = $plugin->fieldConfigService->getByHandle($handle);
                if ($fieldConfig) {
                    $fieldConfig->enablePreviews = $values['enablePreviews'];
                    $fieldConfig->enableTakeover = $values['enableTakeover'];
                    if ($fieldConfig->validate()) {
                        $fieldConfig->save();
                    }
                }
            }
        }

        $fields = $plugin->fieldConfigService->getAllFields();
        $fieldConfigs = $plugin->fieldConfigService->getAll();

        usort($fieldConfigs, function ($a, $b) {
            return strcmp($a->field->name, $b->field->name);
        });

        return $this->renderTemplate('matrix-field-preview/settings/fields', [
            'settings' => $settings,
            'plugin' => $plugin,
            'fields' => $fields,
            'fieldConfigs' => $fieldConfigs
        ]);
    }

    /**
     * Matrix Block Types Settings
     * 
     */
    public function actionBlockTypes()
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        $assets = [
            'success' => Craft::$app->getAssetManager()->getPublishedUrl('@app/web/assets/cp/dist', true, 'images/success.png')
        ];

        $blockTypes = Craft::$app->matrix->getAllBlockTypes();
        $blockTypeConfigs = $plugin->blockTypeConfigService->getAll();

        $blockTypeConfigMap = [];
        foreach ($blockTypeConfigs as $blockTypeConfig) {
            $blockTypeConfigMap[$blockTypeConfig->blockType->id] = $blockTypeConfig;
        }

        $matrixFieldsMap = [];
        foreach ($blockTypes as $blockType) {
            $matrixField = $blockType->field;

            $fieldConfig = $plugin->fieldConfigService->getByHandle($matrixField->handle);

            // Initialise an array for each matrix field
            if (!array_key_exists($matrixField->id, $matrixFieldsMap)) {
                $matrixFieldsMap[$matrixField->id] = [
                    'matrixField' => $matrixField,
                    'fieldConfig' => $fieldConfig,
                    'rows' => []
                ];
            }

            // Get the block type config for this block type if it exists
            $blockTypeConfig = null;
            if (array_key_exists($blockType->id, $blockTypeConfigMap)) {
                $blockTypeConfig = $blockTypeConfigMap[$blockType->id];
            }

            array_push($matrixFieldsMap[$matrixField->id]['rows'], [
                'blockType' => $blockType,
                'blockTypeConfig' => $blockTypeConfig
            ]);
        }

        $matrixFields = [];
        foreach ($matrixFieldsMap as $key => $value) {
            array_push($matrixFields, $value);
        }

        return $this->renderTemplate('matrix-field-preview/settings/block-types', [
            'settings' => $settings,
            'plugin' => $plugin,
            'assets' => $assets,
            'matrixFields' => $matrixFields
        ]);
    }

    /**
     * Matrix Block Type Settings
     * 
     */
    public function actionBlockType($blockTypeId)
    {
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        $siteId = Craft::$app->getSites()->currentSite->id;
        $request = Craft::$app->request;
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $blockType = Craft::$app->matrix->getBlockTypeById($blockTypeId);
        if (!$blockType) {
            throw new NotFoundHttpException('Invalid matrix block type ID: ' . $blockTypeId);
        }

        $blockTypeConfig = $plugin->blockTypeConfigService->getByBlockTypeId($blockTypeId);
        if (!$blockTypeConfig) {
            $blockTypeConfig = new BlockTypeConfigRecord();
            $blockTypeConfig->siteId = $siteId;
            $blockTypeConfig->description = "";
            $blockTypeConfig->fieldId = $blockType->field->id;
            $blockTypeConfig->blockTypeId = $blockType->id;
            $blockTypeConfig->save();
        }

        if ($request->isPost) {
            $post = $request->post();
            $blockTypeConfig->description = $post['settings']['description'];
            if ($blockTypeConfig->validate()) {
                $blockTypeConfig->save();
                Craft::$app->getSession()->setNotice(Craft::t('app', 'Preview saved.'));
                return $this->redirect('matrix-field-preview/settings/block-types');
            } else {
                Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save preview.'));
            }
        }

        return $this->renderTemplate(
            'matrix-field-preview/settings/block-type',
            [
                'blockTypeConfig' => $blockTypeConfig,
                'plugin' => $plugin,
                'fullPageForm' => true,
                'settings' => $settings
            ]
        );
    }

    public function actionNeoFields()
    {
        return $this->renderTemplate('matrix-field-preview/settings/fields', []);
    }

    public function actionNeoBlockTypes()
    {
        return $this->renderTemplate(
            'matrix-field-preview/settings/neo/block-types',
            []
        );
    }

    /**
     *
     */
    public function actionUploadPreviewImage()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        $previewImageService = MatrixFieldPreview::getInstance()->previewImageService;
        $blockTypeConfigService = MatrixFieldPreview::getInstance()->blockTypeConfigService;

        $blockTypeId = Craft::$app->getRequest()->getRequiredBodyParam('blockTypeId');
        $blockTypeConfig = $blockTypeConfigService->getById((int) $blockTypeId);
        if (!$blockTypeConfig) {
            throw new NotFoundHttpException('Invalid preview ID: ' . $blockTypeId);
        }

        if (($file = UploadedFile::getInstanceByName('previewImage')) === null) {
            return null;
        }

        try {
            if ($file->getHasError()) {
                throw new UploadFailedException($file->error);
            }

            // Move to our own temp location
            $fileLocation = Assets::tempFilePath($file->getExtension());
            move_uploaded_file($file->tempName, $fileLocation);

            $previewImageService->savePreviewImage($fileLocation, $blockTypeConfig, $file->name);

            return $this->asJson([
                'html' => $this->_renderPreviewImageTemplate($blockTypeConfig),
            ]);
        } catch (\Throwable $exception) {
            /** @noinspection UnSafeIsSetOverArrayInspection - FP */
            if (isset($fileLocation)) {
                try {
                    FileHelper::unlink($fileLocation);
                } catch (\Throwable $e) {
                    // Let it go
                }
            }

            Craft::error('There was an error uploading the photo: ' . $exception->getMessage(), __METHOD__);

            return $this->asErrorJson(Craft::t('app', 'There was an error uploading your photo: {error}', [
                'error' => $exception->getMessage()
            ]));
        }
    }

    /**
     * 
     */
    public function actionDeletePreviewImage()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        $blockTypeConfigService = MatrixFieldPreview::getInstance()->blockTypeConfigService;
        $blockTypeId = Craft::$app->getRequest()->getRequiredBodyParam('blockTypeId');
        $blockTypeConfig = $blockTypeConfigService->getById((int) $blockTypeId);

        if (!$blockTypeConfig) {
            throw new NotFoundHttpException('Invalid preview ID: ' . $blockTypeId);
        }

        if ($blockTypeConfig->previewImageId) {
            Craft::$app->getElements()->deleteElementById($blockTypeConfig->previewImageId, Asset::class);
        }

        $blockTypeConfig->previewImageId = null;
        $blockTypeConfig->save();

        return $this->asJson([
            'html' => $this->_renderPreviewImageTemplate($blockTypeConfig),
        ]);
    }

    /**
     * 
     */
    private function _renderPreviewImageTemplate($blockTypeConfig): string
    {
        $settings = MatrixFieldPreview::getInstance()->getSettings();
        $view = $this->getView();
        $templateMode = $view->getTemplateMode();
        return $view->renderTemplate('matrix-field-preview/_includes/preview-image-field', [
            'settings' => $settings,
            'blockTypeConfig' => $blockTypeConfig
        ], $templateMode);
    }
}
