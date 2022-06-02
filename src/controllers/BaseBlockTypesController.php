<?php

namespace weareferal\matrixfieldpreview\controllers;

use yii\web\BadRequestHttpException;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;
use weareferal\matrixfieldpreview\records\BlockTypeConfig;

use Craft;
use craft\web\Controller;
use craft\web\UploadedFile;
use craft\errors\UploadFailedException;
use craft\helpers\Assets;
use craft\helpers\FileHelper;
use craft\elements\Asset;


abstract class BaseBlockTypesController extends Controller {

    /**
     * Enfore admin privileges
     * 
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin();
        return parent::beforeAction($action);
    }

    /**
     * List all block type configurations
     * 
     */
    public function actionIndex($templateVars = [])
    {
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);
        $fieldsConfigService = $this->getFieldsConfigService($plugin);
    
        $assets = [
            'success' => Craft::$app->getAssetManager()->getPublishedUrl('@app/web/assets/cp/dist', true, 'images/success.png')
        ];

        // Assemble the fields and field configs
        $fields = [];
        foreach ($fieldsConfigService->getAllFields() as $field) {
            $fieldConfig = $fieldsConfigService->getOrCreateByFieldHandle($field->handle);
            array_push($fields, [
                'field' => $field,
                'fieldConfig' => $fieldConfig,
                'blockTypeConfigs' => $blockTypeConfigService->getOrCreateByFieldHandle($field->handle)
            ]);
        }

        return $this->renderTemplate($this->getIndexTemplate(), [
            'assets' => $assets,
            'fields' => $fields
        ]);
    }

    /**
     * Edit a block type configuration
     * 
     * Note that we receive the "blockTypeId" not the "blockTypeConfigId". This
     * is because at the time of loading this action we don't know if the
     * block type configuration actually exists yet.
     */
    public function actionEdit(int $blockTypeId, $blockTypeConfig = null)
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = MatrixFieldPreview::getInstance()->getSettings();
        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);

        $uploadAction = $this->getUploadAction();
        $deleteAction = $this->getDeleteAction();
    
        // Set some frontend variables to help with JavaScript
        $this->view->registerJsVar('uploadImageUrl', $uploadAction);
        $this->view->registerJsVar('deleteImageUrl', $deleteAction);
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        if (! $blockTypeConfig) {
            $blockTypeConfig = $blockTypeConfigService->getOrCreateByBlockTypeId($blockTypeId);
        }

        return $this->renderTemplate($this->getEditTemplate(), [
            'blockType' => $blockTypeConfig->blockType,
            'blockTypeConfig' => $blockTypeConfig,
            'settings' => $settings
        ]);
    }

    /**
     * Save a block type configuration
     * 
     */
    public function actionSave() {
        $this->requirePostRequest();
        
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);

        $blockTypeId = $this->request->getBodyParam('blockTypeId');

        $blockTypeConfig = $blockTypeConfigService->getOrCreateByBlockTypeId($blockTypeId);
        if (!$blockTypeConfig) {
            throw new BadRequestHttpException("Invalid block type ID: $blockTypeId");
        }

        $blockTypeConfig->description = $this->request->getBodyParam('description');

        if (! $blockTypeConfigService->save($blockTypeConfig)) {
            $this->setFailFlash(Craft::t('matrix-field-preview', 'Couldn\'t save the preview.'));

            // Send user back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'blockTypeConfig' => $blockTypeConfig,
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('matrix-field-preview', 'Preview saved.'));
        return $this->redirectToPostedUrl($blockTypeConfig);
    }

    /**
     * Upload a preview to a block type configuration
     * 
     */
    protected function actionUploadPreview()
    {
        $this->requireAcceptsJson();
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        $plugin = MatrixFieldPreview::getInstance();
        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);

        $previewImageService = MatrixFieldPreview::getInstance()->previewImageService;
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
                'html' => $this->_renderPreviewPartialTemplate($blockTypeConfig),
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
     * Delete a preview belonging to a block type configuration
     * 
     */
    public function actionDeletePreview()
    {
        $this->requireAcceptsJson();

        $plugin = MatrixFieldPreview::getInstance();
        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);

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
            'html' => $this->_renderPreviewPartialTemplate($blockTypeConfig),
        ]);
    }

    private function _renderPreviewPartialTemplate($blockTypeConfig): string
    {
        $settings = MatrixFieldPreview::getInstance()->getSettings();
        $view = $this->getView();
        $templateMode = $view->getTemplateMode();
        return $view->renderTemplate('matrix-field-preview/_includes/settings/preview-image-field', [
            'settings' => $settings,
            'blockTypeConfig' => $blockTypeConfig
        ], $templateMode);
    }

    protected function getFieldsConfigService($plugin)
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented')); 
    }

    protected function getBlockTypeConfigService($plugin)
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented')); 
    }

    protected function getIndexTemplate()
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getEditTemplate()
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getUploadAction()
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getDeleteAction()
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }
}