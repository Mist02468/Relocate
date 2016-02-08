<?php

use Phinx\Migration\AbstractMigration;

class AddCityIdAndLatLongToCompany extends AbstractMigration
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
        // create the company location table
        $table = $this->table('companyLocation');
        $table->addColumn('company_id', 'integer')
              ->addForeignKey('company_id', 'company', 'id', array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
              ->addColumn('coordinatesNorth', 'decimal', array('precision' => 10, 'scale' => 6))
              ->addColumn('coordinatesWest', 'decimal', array('precision' => 10, 'scale' => 6))
              ->create();
    }
}
