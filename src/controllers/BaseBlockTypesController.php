<?php

namespace weareferal\matrixfieldpreview\controllers;

use yii\web\BadRequestHttpException;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;

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

    // public function actionEdit() {
    //     $request = Craft::$app->request;
    //     $plugin = MatrixFieldPreview::getInstance();
    //     $settings = $plugin->getSettings();
        
    //     $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);
    //     $fieldsConfigService = $this->getFieldsConfigService($plugin);
        
    //     $this->view->registerJsVar('uploadImageUrl', $this->getRoutePrefix + "/upload");
    //     $this->view->registerJsVar('deleteImageUrl', $this->getRoutePrefix + "/delete");
    //     $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

    //     // First check that block type is valid
    //     $blockType = $blockTypeConfigService->getBlockTypeById($blockTypeId);
    //     if (!$blockType) {
    //         throw new NotFoundHttpException('Invalid matrix block type ID: ' . $blockTypeId);
    //     }

    //     $blockTypeConfig = $blockTypeConfigService->getOrCreateByBlockTypeId($blockTypeId);

    //     if ($request->isPost) {
    //         $post = $request->post();
    //         $blockTypeConfig->description = $post['settings']['description'];
    //         if ($blockTypeConfig->validate()) {
    //             $blockTypeConfig->save();
    //             Craft::$app->getSession()->setNotice(Craft::t('app', 'Preview saved.'));
    //             return $this->redirect($this->getRoutePrefix + "/index");
    //         } else {
    //             Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save preview.'));
    //         }
    //     }

    //     return $this->renderTemplate(
    //         $this->getRoutePrefix + "/index",
    //         array_merge($templateVars, [
    //             'blockTypeConfig' => $blockTypeConfig,
    //             'plugin' => $plugin,
    //             'fullPageForm' => true,
    //             'settings' => $settings
    //         ])
    //     );
    // }

    public function actionEdit(int $blockTypeId)
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

        $blockTypeConfig = $blockTypeConfigService->getOrCreateByBlockTypeId($blockTypeId);

        return $this->renderTemplate($this->getEditTemplate(), [
            'blockTypeConfig' => $blockTypeConfig,
            'settings' => $settings
        ]);
    }

    /**
     * Upload
     * 
     */
    protected function actionUpload()
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
     * 
     */
    public function actionDelete($blockTypeConfigService)
    {
        $this->requireAcceptsJson();
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

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
            'html' => $this->_renderPreviewImageTemplate($blockTypeConfig),
        ]);
    }

    private function _renderPreviewImageTemplate($blockTypeConfig): string
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
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented')); 
    }

    protected function getBlockTypeConfigService($plugin)
    {
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented')); 
    }

    protected function getIndexTemplate()
    {
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getEditTemplate()
    {
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getUploadAction()
    {
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getDeleteAction()
    {
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented'));
    }
}