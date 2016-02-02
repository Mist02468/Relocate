<?php

use Phinx\Migration\AbstractMigration;

class ChangeDecimalPrecisionForCityColumns extends AbstractMigration
{
     public function up()
    {
        $table = $this->table('cities');
        $table->changeColumn('landAreaSquareMiles', 'decimal', array('precision' => 10, 'scale' => 2))
              ->changeColumn('coordinatesNorth', 'decimal', array('precision' => 10, 'scale' => 4))
              ->changeColumn('coordinatesWest', 'decimal', array('precision' => 10, 'scale' => 4))
              ->update();
    }
    
    public function down()
    {
        $table = $this->table('cities');
        $table->changeColumn('landAreaSquareMiles', 'decimal', array('precision' => 10, 'scale' => 0))
              ->changeColumn('coordinatesNorth', 'decimal', array('precision' => 10, 'scale' => 0))
              ->changeColumn('coordinatesWest', 'decimal', array('precision' => 10, 'scale' => 0))
              ->update();
    }
}
