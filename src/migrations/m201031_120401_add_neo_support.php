<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201031_120401_add_neo_support migration.
 */
class m201031_120401_add_neo_support extends Migration
{
    public function safeUp()
    {
        // Neo support
        if (Craft::$app->plugins->getPlugin("neo", false)->isInstalled) {
            if ($this->createNeoFieldTables()) {
                $this->addNeoFieldForeignKeys();
                Craft::$app->db->schema->refresh();
            }
        }
        return true;
    }

    public function safeDown()
    {
        if (Craft::$app->plugins->getPlugin("neo", false)->isInstalled) {
            $this->removeNeoFieldTables();
        }
        return true;
    }

    protected function createNeoFieldTables()
    {
        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_neo_fields}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_neo_fields}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'fieldId' => $this->integer()->notNull(),
                    'enablePreviews' => $this->boolean(true),
                    'enableTakeover' => $this->boolean(true)
                ]
            );
        }

        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_neo_blocktypes}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_neo_blocktypes}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'blockTypeId' => $this->integer()->notNull(),
                    'fieldId' => $this->integer()->notNull(),
                    'previewImageId' => $this->integer(),
                    'description' => $this->string(1024)->notNull()->defaultValue(''),
                ]
            );
        }
    }

    protected function addNeoFieldForeignKeys()
    {
        // Neo fields
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_fields}}', 'siteId'),
            '{{%matrixfieldpreview_neo_fields}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_fields}}', 'fieldId'),
            '{{%matrixfieldpreview_neo_fields}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Neo blocktypes
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes}}', 'fieldId'),
            '{{%matrixfieldpreview_neo_blocktypes}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes}}', 'blockTypeId'),
            '{{%matrixfieldpreview_neo_blocktypes}}',
            'blockTypeId',
            '{{%neoblocktypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes}}', 'previewImageId'),
            '{{%matrixfieldpreview_neo_blocktypes}}',
            'previewImageId',
            '{{%assets}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    protected function removeNeoFieldTables()
    {
        $this->dropTableIfExists('{{%matrixfieldpreview_neo_blocktypes}}');
        $this->dropTableIfExists('{{%matrixfieldpreview_neo_fields}}');
    }
}
