<?php

/**
 * Matrix Field Preview plugin for Craft CMS 3.x
 *
 * Gives you the ability to configure a preview for all your matrix field blocks, giving your clients a better publishing experience.
 *
 * @link      https://weareferal.com
 * @copyright Copyright (c) 2020 Timmy O'Mahony 
 */

namespace weareferal\matrixfieldpreview;

use weareferal\matrixfieldpreview\services\PreviewService;
use weareferal\matrixfieldpreview\services\PreviewImageService;
use weareferal\matrixfieldpreview\models\Settings;
use weareferal\matrixfieldpreview\fields\MatrixWithPreview;
use weareferal\matrixfieldpreview\utilities\MatrixFieldPreviewUtility as MatrixFieldPreviewUtilityUtility;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Utilities;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\fields\Assets;
use craft\fields\Matrix;
use craft\services\Fields;
use craft\elements\MatrixBlock;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Timmy O'Mahony 
 * @package   MatrixFieldPreview
 * @since     1.0.0
 *
 * @property  PreviewService $previewService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class MatrixFieldPreview extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * MatrixFieldPreview::$plugin
     *
     * @var MatrixFieldPreview
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * MatrixFieldPreview::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our services
        $this->setComponents([
            'previewService' => PreviewService::class,
            'previewImageService' => PreviewImageService::class
        ]);

        // Register our custom matrix field
        // Event::on(
        //     Fields::class,
        //     Fields::EVENT_REGISTER_FIELD_TYPES,
        //     function(RegisterComponentTypesEvent $event) {
        //         // ArrayHelper::remove($event->types, Matrix::class);
        //         $event->types[] = MatrixWithPreview::class;
        //     });






        // Register our site routes
        // Event::on(
        //     UrlManager::class,
        //     UrlManager::EVENT_REGISTER_SITE_URL_RULES,
        //     function (RegisterUrlRulesEvent $event) {
        //         $event->rules['siteActionTrigger1'] = 'matrix-field-preview/default';
        //     }
        // );

        // Register our CP routes
        // Event::on(
        //     UrlManager::class,
        //     UrlManager::EVENT_REGISTER_CP_URL_RULES,
        //     function (RegisterUrlRulesEvent $event) {
        //         $event->rules = array_merge(
        //             $event->rules,
        //             [
        //                 #'matrix-field-previews/block-type/<blockTypeId:\d>' => 'matridx-field-preview/block-type/block-type',
        //                 'matrix-field-preview/upload-preview-image' => 'matrix-field-preview/preview-image/upload-preview-image',
        //                 'matrix-field-preview/delete-preview-image' => 'matrix-field-preview/preview-image/delete-preview-image'
        //             ]
        //         );
        //     }
        // );

        // Register our utilities
        // Event::on(
        //     Utilities::class,
        //     Utilities::EVENT_REGISTER_UTILITY_TYPES,
        //     function (RegisterComponentTypesEvent $event) {
        //         $event->types[] = MatrixFieldPreviewUtilityUtility::class;
        //     }
        // );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {


                    // Find all matrix fields and add a "previewEnabled" key
                    // to their JSON settings in the fields table
                }
            }
        );

        /**
         * Logging in Craft involves using one of the following methods:
         *
         * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
         * Craft::info(): record a message that conveys some useful information.
         * Craft::warning(): record a warning message that indicates something unexpected has happened.
         * Craft::error(): record a fatal error that should be investigated as soon as possible.
         *
         * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
         *
         * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
         * the category to the method (prefixed with the fully qualified class name) where the constant appears.
         *
         * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
         * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
         *
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t(
                'matrix-field-preview',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('matrix-field-preview/settings', [
            'settings' => $this->getSettings(),
            'blockTypes' => Craft::$app->matrix->getAllBlockTypes()
        ]);
    }
}
