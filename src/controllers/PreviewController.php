<?php


namespace weareferal\matrixfieldpreview\controllers;

use craft\records\Entry;
use vaersaagod\matrixmate\MatrixMate;
use weareferal\matrixfieldpreview\MatrixFieldPreview;

use Craft;
use craft\web\Controller;

use yii\web\NotFoundHttpException;
use yii\helpers\Markdown;

/**
 * Preview controller
 *
 * Controller to handle Ajax requests for configuration from the cp
 */
class PreviewController extends Controller
{

	protected array|bool|int $allowAnonymous = [];

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
						"blockTypes" => [],
						"categories" => []
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

		// Add field info
		$response['config']['field'] = [
				"name" => $fieldConfig->field->name,
				"handle" => $fieldConfig->field->handle,
				"enablePreviews" => $fieldConfig->enablePreviews,
				"enableTakeover" => $fieldConfig->enableTakeover,
				// TODO: make this configurable via the settings
				"buttonText" => "Content Previews"
		];

		// Add categories
		foreach ($plugin->categoryService->getAll() as $category) {
			array_push($response['config']["categories"], [
					'id' => $category->id,
					'name' => $category->name,
					'description' => $category->description,
					"descriptionHTML" => Markdown::process($category->description)
			]);
		}

		// Add block type preview info
		$blockTypeConfigs = $blockTypeService->getOrCreateByFieldHandle($fieldHandle);
		foreach ($blockTypeConfigs as $blockTypeConfig) {
			$blockType = $blockTypeConfig->blockType;
			if(Craft::$app->plugins->isPluginInstalled('matrixmate')) {
				$matrixmate = MatrixMate::getInstance()->getSettings();
				$entry_id = intval(basename(parse_url(Craft::$app->request->getReferrer(), PHP_URL_PATH)));
				$site_ids =  Craft::$app->sites->getAllSiteIds();
				$entry = Craft::$app->entries->getEntryById($entry_id, $site_ids, ['status' => array('live', 'pending', 'disabled', 'expired')]);

				if(isset($entry))  {
					$section_handle = $entry->getSection()->handle;
					$skip = false;
					if(isset($matrixmate->fields[$response['config']["field"]['handle']])) {
						foreach($matrixmate->fields[$response['config']["field"]['handle']] as $key => $value) {
							foreach ($value['hiddenTypes'] as $hiddentype) {
								if($hiddentype == $blockType->handle && isset(explode(':',$key)[1]) && $section_handle == explode(':',$key)[1]) $skip = true;
							}
						}
					}
					if($skip) continue;
				}
			}

			$result = [
					"name" => $blockType->name,
					"handle" => $blockType->handle,
					"description" => $blockTypeConfig->description,
					"descriptionHTML" => Markdown::process($blockTypeConfig->description),
					"categoryId" => $blockTypeConfig->categoryId,
					"image" => null,
					"thumb" => null
			];
			if ($blockTypeConfig->previewImageId) {
				$asset = Craft::$app->assets->getAssetById($blockTypeConfig->previewImageId);
				$result["imageId"] = $blockTypeConfig->previewImageId;
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
