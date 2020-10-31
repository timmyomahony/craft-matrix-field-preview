<?php

namespace weareferal\matrixfieldpreview\migrations;


use Craft;
use craft\db\Migration;


class Install extends Migration
{
    public $driver;

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        if ($this->createMatrixFieldTables()) {
            $this->addMatrixFieldForeignKeys();
            Craft::$app->db->schema->refresh();
        }

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
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeMatrixFieldTables();
        if (Craft::$app->plugins->getPlugin("neo", false)->isInstalled) {
            $this->removeNeoFieldTables();
        }
        return true;
    }


    protected function createMatrixFieldTables()
    {
        // Matrix fields
        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_fields_config}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_fields_config}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'fieldId' => $this->integer()->notNull(),
                    'enablePreviews' => $this->boolean(true),
                    'enableTakeover' => $this->boolean(true)
                ]
            );
        }

        // Matrix block types
        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_blocktypes_config}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_blocktypes_config}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'blockTypeId' => $this->integer()->notNull(),
                    'fieldId' => $this->integer()->notNull(),
                    'previewImageId' => $this->integer(),
                    'siteId' => $this->integer()->notNull(),
                    'description' => $this->string(1024)->notNull()->defaultValue(''),
                ]
            );
        }
        return true;
    }

    protected function addMatrixFieldForeignKeys()
    {
        // Matrix fields
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

        // Matrix block types
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', 'siteId'),
            '{{%matrixfieldpreview_blocktypes_config}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', 'fieldId'),
            '{{%matrixfieldpreview_blocktypes_config}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', 'blockTypeId'),
            '{{%matrixfieldpreview_blocktypes_config}}',
            'blockTypeId',
            '{{%matrixblocktypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', 'previewImageId'),
            '{{%matrixfieldpreview_blocktypes_config}}',
            'previewImageId',
            '{{%assets}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    protected function removeMatrixFieldTables()
    {
        $this->dropTableIfExists('{{%matrixfieldpreview_previewrecord}}');
        $this->dropTableIfExists('{{%matrixfieldpreview_blocktypes_config}}');
        $this->dropTableIfExists('{{%matrixfieldpreview_fields_config}}');
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
