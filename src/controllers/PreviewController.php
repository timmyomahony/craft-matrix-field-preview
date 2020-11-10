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
    public function actionGetPreviews($type, $fieldHandle)
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        $response = [
            "success" => false,
            "config" => [
                "general" => [],
                "field" => null,
                "blockTypes" => []
            ]
        ];

        switch ($type) {
            case "matrix":
                $fieldService = $plugin->matrixFieldConfigService;
                $blockTypeService = $plugin->matrixBlockTypeConfigService;
                break;
            case "neo":
                $fieldService = $plugin->neoFieldConfigService;
                $blockTypeService = $plugin->neoBlockTypeConfigService;
                break;
            default:
                $response["error"] = "'type' must be 'matrix' or 'neo'";
                return $this->asJson($response);
        }


        $fieldConfig = $fieldService->getOrCreateByFieldHandle($fieldHandle);

        if (!$fieldConfig) {
            return $this->asJson($response);
        }

        $response['config']['field'] = [
            "name" => $fieldConfig->field->name,
            "handle" => $fieldConfig->field->handle,
            "enablePreviews" => $fieldConfig->enablePreviews,
            "enableTakeover" => $fieldConfig->enableTakeover,
            // TODO: make this configurable via the settings
            "buttonText" => "Content Previews"
        ];

        $blockTypeConfigs = $blockTypeService->getOrCreateByFieldHandle($fieldHandle);
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
            $response['config']["blockTypes"][$blockType->handle] = $result;
        }

        $response["success"] = true;

        return $this->asJson($response);
    }
}
