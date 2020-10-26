<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201026_153002_rename_preview_table migration.
 */
class m201026_153002_rename_preview_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameTable('matrixfieldpreview_previewrecord', 'matrixfieldpreview_blocktypes_config');
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->renameTable('matrixfieldpreview_blocktypes_config', 'matrixfieldpreview_previewrecord');
        return true;
    }
}
