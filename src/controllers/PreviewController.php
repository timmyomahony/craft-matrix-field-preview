<?php


namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;

use Craft;
use craft\web\Controller;

use yii\web\NotFoundHttpException;

/**
 * Preview controller
 * 
 * Controller to handle Ajax requests for configuration from the cp
 */
class PreviewController extends Controller
{

    protected $allowAnonymous = [];

    /**
     * Get preview config 
     * 
     * Return a JSON configuration for the frontend to use
     * 
     * NOTE: there are two "handles" in play here: the matrix field handle
     * as well as the block type handles
     */
    public function actionGetPreviews($matrixFieldHandle)
    {
        $plugin = MatrixFieldPreview::getInstance();
        $fieldConfig = $plugin->matrixFieldConfigService->getOrCreateByFieldHandle($matrixFieldHandle);

        $response = [
            "success" => false,
            "fieldConfig" => null,
            "blockTypeConfigs" => []
        ];

        if (!$fieldConfig) {
            return $this->asJson($response);
        }

        $response['fieldConfig'] = [
            "name" => $fieldConfig->field->name,
            "handle" => $fieldConfig->field->handle,
            "enablePreviews" => $fieldConfig->enablePreviews,
            "enableTakeover" => $fieldConfig->enableTakeover,
        ];

        $blockTypeConfigs = $plugin->matrixBlockTypeConfigService->getOrCreateByFieldHandle($matrixFieldHandle);
        foreach ($blockTypeConfigs as $blockTypeConfig) {
            $blockType = $blockTypeConfig->blockType;
            $result = [
                "name" => $blockType->name,
                "handle" => $blockType->handle,
                "description" => $blockTypeConfig->description,
                "image" => null,
                "thumb" => null
            ];
            if ($blockTypeConfig->previewImageId) {
                $asset = Craft::$app->assets->getAssetById($blockTypeConfig->previewImageId);
                $result["image"] = $asset ? $asset->getUrl([
                    "width" => 800,
                    "mode" => "stretch",
                    "position" => "center-center"
                ]) : "";
                $result["thumb"] = $asset ? $asset->getThumbUrl(300, 300) : "";
            }
            $response["blockTypeConfigs"][$blockType->handle] = $result;
        }

        $response["success"] = true;

        return $this->asJson($response);
    }
}
