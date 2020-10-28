<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Field;

use weareferal\matrixfieldpreview\MatrixFieldPreview;

/**
 * m201026_153926_migrate_block_type_handle_data migration.
 */
class m201026_153926_migrate_block_type_handle_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // The BlockTypeConfigRecord has both a matrixFieldHandle AND a 
        // fieldId. We only need the latter. Before deleting the matrixFieldHandle
        // though we need to make sure the fieldId has been saved
        $plugin = MatrixFieldPreview::getInstance();
        foreach ($plugin->blockTypeConfigService->getAll() as $blockTypeConfig) {
            $matrixField = Craft::$app->getFields()->getFieldByHandle($blockTypeConfig->matrixFieldHandle);
            $blockTypeConfig->fieldId = $matrixField->id;
            $blockTypeConfig->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
