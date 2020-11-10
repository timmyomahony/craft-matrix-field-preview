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

        return $this->renderTemplate('matrix-field-preview/settings/general', [
            'settings' => $settings,
            'plugin' => $plugin
        ]);
    }

    /**
     * Matrix Fields Settings
     * 
     */
    public function actionMatrixFields()
    {
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionFields(
            $plugin->matrixFieldConfigService,
            'matrix-field-preview/settings/matrix-fields'
        );
    }

    /**
     * Neo Fields Settings
     */
    public function actionNeoFields()
    {
        if (!Craft::$app->plugins->isPluginEnabled("neo")) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }

        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionFields(
            $plugin->neoFieldConfigService,
            'matrix-field-preview/settings/neo-fields'
        );
    }

    /**
     * Base Field Settings
     */
    private function _actionFields($fieldService, $template, $templateVars = [])
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        $request = Craft::$app->request;

        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

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

    /**
     * Matrix Block Types Settings
     * 
     */
    public function actionMatrixBlockTypes()
    {
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionBlockTypes(
            $plugin->matrixBlockTypeConfigService,
            $plugin->matrixFieldConfigService,
            'matrix-field-preview/settings/matrix-block-types'
        );
    }

    public function actionNeoBlockTypes()
    {
        if (!Craft::$app->plugins->isPluginEnabled("neo")) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }

        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionBlockTypes(
            $plugin->neoBlockTypeConfigService,
            $plugin->neoFieldConfigService,
            'matrix-field-preview/settings/neo-block-types'
        );
    }

    private function _actionBlockTypes($blockTypeConfigService, $fieldConfigService, $template, $templateVars = [])
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

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

    /**
     * Matrix Block Type Settings
     * 
     */
    public function actionMatrixBlockType($blockTypeId)
    {
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionBlockType(
            $blockTypeId,
            $plugin->matrixBlockTypeConfigService,
            'matrix-field-preview/settings/matrix-upload-preview-image',
            'matrix-field-preview/settings/matrix-delete-preview-image',
            'matrix-field-preview/settings/matrix-block-types',
            'matrix-field-preview/settings/matrix-block-type'
        );
    }

    public function actionNeoBlockType($blockTypeId)
    {
        if (!Craft::$app->plugins->isPluginEnabled("neo")) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }

        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionBlockType(
            $blockTypeId,
            $plugin->neoBlockTypeConfigService,
            'matrix-field-preview/settings/neo-upload-preview-image',
            'matrix-field-preview/settings/neo-delete-preview-image',
            'matrix-field-preview/settings/neo-block-types',
            'matrix-field-preview/settings/neo-block-type'
        );
    }

    private function _actionBlockType(
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

    public function actionMatrixUploadPreviewImage()
    {
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionDeletePreviewImage(
            $plugin->matrixBlockTypeConfigService
        );
    }

    public function actionNeoUploadPreviewImage()
    {
        if (!Craft::$app->plugins->isPluginEnabled("neo")) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }

        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionUploadPreviewImage(
            $plugin->neoBlockTypeConfigService
        );
    }

    public function _actionUploadPreviewImage($blockTypeConfigService)
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

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

    public function actionMatrixDeletePreviewImage()
    {
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionDeletePreviewImage(
            $plugin->matrixBlockTypeConfigService
        );
    }

    public function actionNeoDeletePreviewImage()
    {
        if (!Craft::$app->plugins->isPluginEnabled("neo")) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }

        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionDeletePreviewImage(
            $plugin->neoBlockTypeConfigService
        );
    }

    private function _actionDeletePreviewImage($blockTypeConfigService)
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

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
        return $view->renderTemplate('matrix-field-preview/_includes/settings/preview-image-field', [
            'settings' => $settings,
            'blockTypeConfig' => $blockTypeConfig
        ], $templateMode);
    }
}
