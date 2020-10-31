<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201031_132510_remove_unused_site_id migration.
 */
class m201031_132510_remove_unused_site_id extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // try {
        //     $this->dropForeignKey("matrixfieldpreview_previewrecord_siteId_fk", "{{%matrixfieldpreview_blocktypes_config}}");
        // } catch (Exception $e) {
        // }

        // try {
        //     $this->dropForeignKey("matrixfieldpreview_blocktypes_config_siteId_fk", "{{%matrixfieldpreview_blocktypes_config}}");
        // } catch (Exception $e) {
        // }

        // $this->dropForeignKey("matrixfieldpreview_fields_config_siteId_fk", "{{%matrixfieldpreview_fields_config}}");
        $this->dropColumn("{{%matrixfieldpreview_blocktypes_config}}", "siteId");
        $this->dropColumn("{{%matrixfieldpreview_fields_config}}", "siteId");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
