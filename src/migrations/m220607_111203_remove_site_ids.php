<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220607_111203_remove_site_ids migration.
 */
class m220607_111203_remove_site_ids extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $blockTypeConfigTable = Craft::$app->db->schema->getTableSchema("{{%matrixfieldpreview_blocktypes_config}}");
        if (isset($blockTypeConfigTable->columns["siteId"])) {
            $this->dropColumn(
                "{{%matrixfieldpreview_blocktypes_config}}",
                "siteId"
            );
        }
        $fieldConfigTable = Craft::$app->db->schema->getTableSchema("{{%matrixfieldpreview_fields_config}}");
        if (isset($fieldConfigTable->columns["siteId"])) {
            $this->dropColumn(
                "{{%matrixfieldpreview_fields_config}}",
                "siteId"
            );
        }
        Craft::$app->db->schema->refresh();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
