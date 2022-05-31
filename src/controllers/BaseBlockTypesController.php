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
    protected $allowAnonymous = [];

    protected function _actionIndex($blockTypeConfigService, $fieldConfigService, $template, $templateVars = [])
    {
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $assets = [
            'success' => Craft::$app->getAssetManager()->getPublishedUrl('@app/web/assets/cp/dist', true, 'images/success.png')
        ];

        $fields = [];
        foreach ($fieldConfigService->getAllFields() as $field) {
            $fieldConfig = $fieldConfigService->getOrCreateByFieldHandle($field->handle);
            array_push($fields, [
                'field' => $field,
                'fieldConfig' => $fieldConfig,
                'blockTypeConfigs' => $blockTypeConfigService->getOrCreateByFieldHandle($field->handle)
            ]);
        }

        return $this->renderTemplate($template, array_merge($templateVars, [
            'settings' => $settings,
            'plugin' => $plugin,
            'assets' => $assets,
            'fields' => $fields
        ]));
    }

    protected function _actionEdit(
        $blockTypeId,
        $blockTypeConfigService,
        $uploadImageUrl,
        $deleteImageUrl,
        $redirect,
        $template,
        $templateVars = []
    ) {
        $this->view->registerJsVar('uploadImageUrl', $uploadImageUrl);
        $this->view->registerJsVar('deleteImageUrl', $deleteImageUrl);
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        $request = Craft::$app->request;
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        // First check that block type is valid
        $blockType = $blockTypeConfigService->getBlockTypeById($blockTypeId);
        if (!$blockType) {
            throw new NotFoundHttpException('Invalid matrix block type ID: ' . $blockTypeId);
        }

        $blockTypeConfig = $blockTypeConfigService->getOrCreateByBlockTypeId($blockTypeId);

        if ($request->isPost) {
            $post = $request->post();
            $blockTypeConfig->description = $post['settings']['description'];
            if ($blockTypeConfig->validate()) {
                $blockTypeConfig->save();
                Craft::$app->getSession()->setNotice(Craft::t('app', 'Preview saved.'));
                return $this->redirect($redirect);
            } else {
                Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save preview.'));
            }
        }

        return $this->renderTemplate(
            $template,
            array_merge($templateVars, [
                'blockTypeConfig' => $blockTypeConfig,
                'plugin' => $plugin,
                'fullPageForm' => true,
                'settings' => $settings
            ])
        );
    }

    protected function _actionUpload($blockTypeConfigService)
    {
        $this->requireAcceptsJson();
        $this->requireLogin();
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

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

    protected function _actionDelete($blockTypeConfigService)
    {
        $this->requireAcceptsJson();
        $this->requireLogin();
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

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

    protected function _renderPreviewImageTemplate($blockTypeConfig): string
    {
        $settings = MatrixFieldPreview::getInstance()->getSettings();
        $view = $this->getView();
        $templateMode = $view->getTemplateMode();
        return $view->renderTemplate('matrix-field-preview/_includes/settings/preview-image-field', [
            'settings' => $settings,
            'blockTypeConfig' => $blockTypeConfig
        ], $templateMode);
    }
}