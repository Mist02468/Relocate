<?php

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // create the states table
        $table = $this->table('states');
        $table->addColumn('name', 'string')
              ->create();
              
        // create the cities table
        $table = $this->table('cities');
        $table->addColumn('name', 'string')
              ->addColumn('state_id', 'integer')
              ->addForeignKey('state_id', 'states', 'id', array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
              ->addColumn('walkScore', 'integer')
              ->addColumn('avgTemp', 'decimal')
              ->create();
              
        // create the companies table
        $table = $this->table('companies');
        $table->addColumn('name', 'string')
              ->create();
    }
}
