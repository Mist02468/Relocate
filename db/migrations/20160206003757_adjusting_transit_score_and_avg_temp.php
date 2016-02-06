<?php

use Phinx\Migration\AbstractMigration;

class AdjustingTransitScoreAndAvgTemp extends AbstractMigration
{
    //moving transitScore (by removing and then adding again, with the after option) after walkScore
    //changing the avgTemp column to keep 1 decimal point and only space for 5 digits
    public function up()
    {
        $table = $this->table('city');
        $table->removeColumn('transitScore')
              ->update();
        $table->addColumn('transitScore', 'integer', array('after' => 'walkScore'))
              ->update();
        $table->changeColumn('avgTemp', 'decimal', array('precision' => 5, 'scale' => 1))
              ->update();
    }

    public function down()
    {
        $table = $this->table('city');
        $table->removeColumn('transitScore')
              ->update();
        $table->addColumn('transitScore', 'integer')
              ->update();
        $table->changeColumn('avgTemp', 'decimal', array('precision' => 10, 'scale' => 0))
              ->update();
    }
}
