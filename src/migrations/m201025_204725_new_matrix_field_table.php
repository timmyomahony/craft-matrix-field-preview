<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201025_204725_new_matrix_field_table migration.
 */
class m201025_204725_new_matrix_field_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->createTables()) {
            $this->addForeignKeys();
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
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_fields_config}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%matrixfieldpreview_fields_config}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    // Custom columns in the table
                    'fieldId' => $this->integer()->notNull(),
                    'enablePreviews' => $this->boolean(true),
                    'enableTakeover' => $this->boolean(true)
                ]
            );
        }

        return $tablesCreated;
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_fields_config}}', 'siteId'),
            '{{%matrixfieldpreview_fields_config}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_fields_config}}', 'fieldId'),
            '{{%matrixfieldpreview_fields_config}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    protected function removeTables()
    {
        // matrixfieldpreview_previewrecord table
        $this->dropTableIfExists('{{%matrixfieldpreview_fields_config}}');
    }
}
