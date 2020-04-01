<?php

namespace weareferal\matrixfieldpreview\migrations;


use Craft;
use craft\config\DbConfig;
use craft\db\Migration;


class Install extends Migration
{
    public $driver;

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }


    protected function createTables()
    {
        $tablesCreated = false;

        // matrixfieldpreview_previewrecord table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_previewrecord}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%matrixfieldpreview_previewrecord}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    // Custom columns in the table
                    'matrixFieldHandle' => $this->string(1024)->notNull()->defaultValue(''),
                    'blockTypeId' => $this->integer()->notNull(),
                    'previewImageId' => $this->integer(),
                    'siteId' => $this->integer()->notNull(),
                    'description' => $this->string(1024)->notNull()->defaultValue(''),
                ]
            );
        }

        return $tablesCreated;
    }

    protected function createIndexes()
    {
        // matrixfieldpreview_previewrecord table
        $this->createIndex(
            $this->db->getIndexName(
                '{{%matrixfieldpreview_previewrecord}}',
                'description',
                true
            ),
            '{{%matrixfieldpreview_previewrecord}}',
            'description',
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    protected function addForeignKeys()
    {
        // matrixfieldpreview_previewrecord table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_previewrecord}}', 'siteId'),
            '{{%matrixfieldpreview_previewrecord}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_previewrecord}}', 'blockTypeId'),
            '{{%matrixfieldpreview_previewrecord}}',
            'blockTypeId',
            '{{%matrixblocktypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_previewrecord}}', 'previewImageId'),
            '{{%matrixfieldpreview_previewrecord}}',
            'previewImageId',
            '{{%assets}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    protected function insertDefaultData()
    {
    }

    protected function removeTables()
    {
        // matrixfieldpreview_previewrecord table
        $this->dropTableIfExists('{{%matrixfieldpreview_previewrecord}}');
    }
}
