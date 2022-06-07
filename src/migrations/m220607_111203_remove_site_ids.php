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
        $blockTypeConfigTable = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_blocktypes_config}}');
        if (isset($blockTypeConfigTable->columns['siteId'])) {
            $this->dropColumn(
                "{{%matrixfieldpreview_blocktypes_config}}",
                "siteId");
        }
        $fieldConfigTable = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_fields_config}}');
        if (isset($fieldConfigTable->columns['siteId'])) {
            $this->dropColumn(
                "{{%matrixfieldpreview_fields_config}}",
                "siteId");
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $blockTypeConfigTable = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_blocktypes_config}}');
        if (! isset($blockTypeConfigTable->columns['siteId'])) {
            $this->addColumn("{{%matrixfieldpreview_blocktypes_config}}", "siteId", "integer");
            $this->addForeignKey(
                $this->db->getForeignKeyName("{{%matrixfieldpreview_blocktypes_config}}", "siteId"),
                "{{%matrixfieldpreview_blocktypes_config}}",
                "siteId",
                "{{%sites}}",
                "id",
                "CASCADE",
                "CASCADE"
            );
        }
        $fieldsConfig = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_fields_config}}');
        if (! isset($fieldsConfig->columns['siteId'])) {
            $this->addColumn("{{%matrixfieldpreview_fields_config}}", "siteId", "integer");
            $this->addForeignKey(
                $this->db->getForeignKeyName("{{%matrixfieldpreview_fields_config}}", "siteId"),
                "{{%matrixfieldpreview_fields_config}}",
                "siteId",
                "{{%sites}}",
                "id",
                "CASCADE",
                "CASCADE"
            );
        }
        return true;
    }
}
