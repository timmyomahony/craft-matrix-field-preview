<?php

/**
 * Matrix Field Preview plugin for Craft CMS 3.x
 *
 * Gives you the ability to configure a preview for all your matrix field blocks, giving your clients a better publishing experience.
 *
 * @link      https://weareferal.com
 * @copyright Copyright (c) 2020 Timmy O'Mahony 
 */

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\records\MatrixFieldPreviewRecord;

use Craft;
use craft\web\Controller;
use craft\services\Matrix;

use yii\web\NotFoundHttpException;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Timmy O'Mahony 
 * @package   MatrixFieldPreview
 * @since     1.0.0
 */
class SettingsController extends Controller
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/matrix-field-preview/default
     *
     * @return mixed
     */
    // public function actionDashboard()
    // {
    //     $this->requireCpRequest();

    //     $plugin = MatrixFieldPreview::getInstance();

    //     $variables = [];
    //     $variables['plugin'] = $plugin;
    //     $variables['settingsHtml'] = $plugin->settingsHtml();
    //     $variables['settings'] = $plugin->getSettings();
    //     $variables['matrixBlockTypes'] = Craft::$app->matrix->getAllBlockTypes();

    //     return $this->renderTemplate(
    //         'matrix-field-preview/settings/dashboard',
    //         $variables
    //     );
    // }

    private function getBlockTypeOr404($blockTypeId)
    {
        $blockType = Craft::$app->matrix->getBlockTypeById($blockTypeId);
        if (!$blockType) {
            throw new NotFoundHttpException('Invalid matrix block type ID: ' . $blockTypeId);
        }
        return $blockType;
    }

    public function actionBlockType($blockTypeId)
    {
        $siteId = Craft::$app->getSites()->currentSite->id;
        $service = MatrixFieldPreview::getInstance()->matrixService;
        $request = Craft::$app->request;
        $plugin = MatrixFieldPreview::getInstance();

        $model = $service->getByBlockTypeId($blockTypeId);
        if (!$model) {
            $model = new MatrixFieldPreviewRecord();
        }

        $blockType = $this->getBlockTypeOr404($blockTypeId);
        $model->blockTypeId = $blockType->id;
        $model->siteId = $siteId;

        if ($request->isPost) {
            $post = $request->post();
            $model->description = $post['description'];
            if ($model->validate()) {
                $model->save();
                Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));
                return $this->redirect('/admin/settings/plugins/' . $plugin->id);
            } else {
                Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save plugin settings.'));
            }
        }

        return $this->renderTemplate(
            'matrix-field-preview/block-type',
            [
                'plugin' => $plugin,
                'model' => $model,
                'fullPageForm' => true
            ]
        );
    }
}
