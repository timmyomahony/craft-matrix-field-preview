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
        $table = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_blocktypes_config}}');
        if (isset($table->columns['siteId'])) {
            $this->dropColumn("{{%matrixfieldpreview_blocktypes_config}}", "siteId");
        }

        $table = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_fields_config}}');
        if (isset($table->columns['siteId'])) {
            $this->dropColumn("{{%matrixfieldpreview_fields_config}}", "siteId");
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
