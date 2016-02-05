<?php

use Phinx\Migration\AbstractMigration;

class RenameTables extends AbstractMigration
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
    //Renaming database tables, from plural to singular, because apparently that is the Zend Framework 2 convention
    public function change()
    {
        $stateTable = $this->table('states');
        $stateTable->rename('state');

        $cityTable = $this->table('cities');
        $cityTable->rename('city');

        $companyTable = $this->table('companies');
        $companyTable->rename('company');
    }
}
