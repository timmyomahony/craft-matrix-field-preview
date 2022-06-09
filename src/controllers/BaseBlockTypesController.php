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
use craft\helpers\UrlHelper;
use craft\elements\Asset;
use craft\helpers\Json;


abstract class BaseBlockTypesController extends Controller {

    /**
     * Enforce admin privileges
     * 
     * But ignore the settings `allowAdminChanges`, allowing users to
     * configure the plugin while on production.
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin($requireAdminChanges=false);
        return parent::beforeAction($action);
    }

    /**
     * List all block type configurations
     * 
     */
    public function actionIndex($templateVars = [])
    {
        // Required for CSS files
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);
        $fieldsConfigService = $this->getFieldsConfigService($plugin);
    
        $fields = [];
        foreach ($fieldsConfigService->getAllFields() as $field) {
            $fieldConfig = $fieldsConfigService->getOrCreateByFieldHandle($field->handle);
            $blockTypeConfigs = $blockTypeConfigService->getOrCreateByFieldHandle($field->handle);

            if (! $fieldConfig->enablePreviews) {
                continue;
            }

            // Tabledata is required for use with the existing Craft.VueAdminTable
            $tableData = [];
            foreach ($blockTypeConfigs as $blockTypeConfig) {
                $url = UrlHelper::url($this->getEditAction((string) $blockTypeConfig->blockType->id));
                $hasPreview = $blockTypeConfig->previewImageId !== null;
                $category = $blockTypeConfig->categoryId !== null ? $blockTypeConfig->category->name : false;
                $description = $blockTypeConfig->description;
                $status = $blockTypeConfig->previewImageId !== null; 
                array_push($tableData, [
                    "id" => $blockTypeConfig->id,
                    "title" => $blockTypeConfig->blockType->name,
                    "hasPreview" => $hasPreview,
                    "description" => $description,
                    "category" => $category,
                    "url" => $url
                ]);
            }

            array_push($fields, [
                'id'=>$field->id,
                'name'=>$field->name,
                'config' => $fieldConfig,
                'blockTypeConfigs' => $blockTypeConfigs,
                'tableData' => $tableData
            ]);
        }

        return $this->renderTemplate($this->getIndexTemplate(), [
            'assets' => [
                'success' => Craft::$app->getAssetManager()->getPublishedUrl('@app/web/assets/cp/dist', true, 'images/success.png'),
                'cancel' => Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/MatrixFieldPreviewSettings/dist/img/cancel.png', true)
            ],
            'fields' => $fields,
        ]);
    }

    /**
     * Reorder block type configs (for a particular field)
     * 
     */
    public function actionReorder()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $plugin = MatrixFieldPreview::getInstance();

        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);
        $blockTypeConfigIds = Json::decode($this->request->getRequiredBodyParam('ids'));
        $blockTypeConfigService->reorder($blockTypeConfigIds);

        return $this->asJson(['success' => true]);
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

        $categories = $plugin->categoryService->getAll();
    
        return $this->renderTemplate($this->getEditTemplate(), [
            'blockType' => $blockTypeConfig->blockType,
            'blockTypeConfig' => $blockTypeConfig,
            'settings' => $settings,
            'categories' => $categories
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
        $blockTypeConfig->categoryId = $this->request->getBodyParam('categoryId');

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
     * This image will be uploaded and stored as an asset, viewable just like
     * any other asset.
     * 
     * NOTE: We are sending the block type config ID, not the block type ID.
     */
    public function actionUploadPreview()
    {
        $this->requireAcceptsJson();
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);

        $plugin = MatrixFieldPreview::getInstance();
        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);

        $previewImageService = MatrixFieldPreview::getInstance()->previewImageService;
        $blockTypeConfigId = Craft::$app->getRequest()->getRequiredBodyParam('blockTypeConfigId');
        $blockTypeConfig = $blockTypeConfigService->getById((int) $blockTypeConfigId);
        if (!$blockTypeConfig) {
            throw new NotFoundHttpException('Invalid preview ID for block type config: ' . $blockTypeConfigId);
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
     * NOTE: We are sending the block type config ID, not the block type ID.
     */
    public function actionDeletePreview()
    {
        $this->requireAcceptsJson();

        $plugin = MatrixFieldPreview::getInstance();
        $blockTypeConfigService = $this->getBlockTypeConfigService($plugin);

        $blockTypeConfigId = Craft::$app->getRequest()->getRequiredBodyParam('blockTypeConfigId');
        $blockTypeConfig = $blockTypeConfigService->getById((int) $blockTypeConfigId);

        if (!$blockTypeConfig) {
            throw new NotFoundHttpException('Invalid preview ID for block type config: ' . $blockTypeConfigId);
        }

        if ($blockTypeConfig->previewImageId) {
            // https://github.com/craftcms/cms/blob/5bc54a47bc2160124e9fe13843a7808e258e4b70/src/services/Elements.php#L1620
            $deleted = Craft::$app->getElements()->deleteElementById($blockTypeConfig->previewImageId, Asset::class, null, true);
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

    protected function getEditAction($id)
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }
}