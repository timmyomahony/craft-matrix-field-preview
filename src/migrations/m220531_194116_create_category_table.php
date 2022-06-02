<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220531_194116_create_category_table migration.
 */
class m220531_194116_create_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->createTables()) {
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTables();
        return true;
    }

    protected function createTables()
    {
        $tablesCreated = false;
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_category}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%matrixfieldpreview_category}}',
                [
                    // Active record defaults
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    // Custom columns in the table
                    'name' => $this->string(100)->notNull()->defaultValue(''),
                    'description' => $this->string(1024)->notNull()->defaultValue(''),
                    'sortOrder' => $this->integer()->notNull()->defaultValue(0),
                ]
            );
        }

        return $tablesCreated;
    }

    protected function removeTables()
    {
        // matrixfieldpreview_previewrecord table
        $this->dropTableIfExists('{{%matrixfieldpreview_category}}');
    }
}
