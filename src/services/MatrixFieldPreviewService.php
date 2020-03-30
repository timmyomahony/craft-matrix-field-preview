<?php
/**
 * Matrix Field Preview plugin for Craft CMS 3.x
 *
 * Gives you the ability to configure a preview for all your matrix field blocks, giving your clients a better publishing experience.
 *
 * @link      https://weareferal.com
 * @copyright Copyright (c) 2020 Timmy O'Mahony 
 */

namespace weareferal\matrixfieldpreview\services;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\records\MatrixFieldPreviewRecord;
// use weareferal\matrixfieldpreview\models\MatrixFieldPreviewModel;

use Craft;
use craft\base\Component;

/**
 * MatrixFieldPreviewService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Timmy O'Mahony 
 * @package   MatrixFieldPreview
 * @since     1.0.0
 */
class MatrixFieldPreviewService extends Component
{
    public function getByBlockTypeId($blockTypeId) {
        $record = MatrixFieldPreviewRecord::findOne([
            'blockTypeId' => $blockTypeId
        ]);

        if (! $record) {
            return null;
        }

        return $record;
    }
}
