<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220602_095344_add_category_foreignkey migration.
 */
class m220602_095344_add_category_foreignkey extends Migration
{
    private function _neoInstalled()
    {
        $neo = Craft::$app->plugins->getPlugin("neo", false);
        return $neo && $neo->isInstalled;
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addCategoryForeignKey('{{%matrixfieldpreview_blocktypes_config}}');

        if ($this->_neoInstalled()) {
            $this->addCategoryForeignKey('{{%matrixfieldpreview_neo_blocktypes_config}}');
        }

        Craft::$app->db->schema->refresh();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeCategoryForeignKey("{{%matrixfieldpreview_blocktypes_config}}");
        if ($this->_neoInstalled()) {
            $this->removeCategoryForeignKey('{{%matrixfieldpreview_neo_blocktypes_config}}');
        }
        return true;
    }

    protected function addCategoryForeignKey($tableName)
    {
        $this->addColumn($tableName, 'categoryId', 'integer');
        $this->alterColumn($tableName, 'categoryId', 'DROP NOT NULL');
        $this->alterColumn($tableName, 'categoryId', 'SET DEFAULT NULL');
        $this->addForeignKey(
            $this->db->getForeignKeyName($tableName, "categoryId"),
            $tableName,
            "categoryId",
            "{{%matrixfieldpreview_category}}",
            "id",
            "SET NULL",
            "SET NULL"
        );
    }

    protected function removeCategoryForeignKey($tableName)
    {
        $this->dropColumn(
            $tableName,
            "categoryId");
    }
}
