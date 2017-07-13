<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloInventoryBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_2_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloInventoryItemTable($schema);
        $this->createMarelloInventoryInventoryLevelTable($schema);
        $this->createMarelloInventoryWarehouseTable($schema);
        $this->createMarelloInventoryWarehouseTypeTable($schema);

        /** Update existing table */
        $this->updateMarelloInventoryWarehouseTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloInventoryItemForeignKeys($schema);
        $this->addMarelloInventoryInventoryLevelForeignKeys($schema);
        $this->addMarelloInventoryWarehouseForeignKeys($schema);
        $this->addMarelloInventoryWarehouseTypeForeignKeys($schema);
    }

    /**
     * Create marello_inventory_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['product_id'], 'idx_40b8d0414584665a', []);
    }

    /**
     * Create marello_inventory_level table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryInventoryLevelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_level');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('inventory', 'integer', []);
        $table->addColumn('inventory_alteration', 'integer', []);
        $table->addColumn('allocated_inventory', 'integer', []);
        $table->addColumn('allocated_inventory_alteration', 'integer', []);
        $table->addColumn('change_trigger', 'string', ['length' => 255]);
        $table->addColumn('subject_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subject_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('inventory_item_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['inventory_item_id'], 'IDX_32D13BA4243D10EA', []);
        $table->addIndex(['user_id'], 'IDX_32D13BA4F675F31B', []);


        $table->addColumn('warehouse_id', 'integer', []);
        $table->addUniqueIndex(['inventory_item_id', 'warehouse_id'], 'uniq_40b8d0414584665a5080ecde');
        $table->addIndex(['warehouse_id'], 'idx_40b8d0415080ecde', []);
    }

    /**
     * Create marello_inventory_warehouse table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryWarehouseTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_warehouse');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', []);
        $table->addColumn('address_id', 'integer', ['notnull' => false]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('is_default', 'boolean', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['address_id'], 'uniq_15597d1f5b7af75');
        $table->addIndex(['owner_id'], 'idx_15597d17e3c61f9', []);
    }

    /**
     * Create marello_inventory_wh_type table
     *
     * @param Schema $schema
     */
    protected function createMarelloInventoryWarehouseTypeTable(Schema $schema)
    {
        if (!$schema->hasTable('marello_inventory_wh_type')) {
            $table = $schema->createTable('marello_inventory_wh_type');
            $table->addColumn('name', 'string', ['length' => 32]);
            $table->addColumn('label', 'string', ['length' => 255]);
            $table->setPrimaryKey(['name']);
            $table->addUniqueIndex(['label'], '');
        }
    }

    /**
     * Add marello_inventory_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_inventory_level foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryInventoryLevelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_level');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_item'),
            ['inventory_item_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_inventory_warehouse foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryWarehouseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Update existing Warehouse table
     * @param Schema $schema
     */
    protected function updateMarelloInventoryWarehouseTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addColumn('warehouse_type', 'string', ['notnull' => false, 'length' => 32]);
        $table->addIndex(['warehouse_type']);
    }

    /**
     * Add marello_inventory_wh_type foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInventoryWarehouseTypeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_wh_type'),
            ['warehouse_type'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
