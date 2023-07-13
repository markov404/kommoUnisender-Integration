<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migration
 * id = 20230621173834
 */
class DeleteEmailsTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        Capsule::schema()->dropIfExists('emails');
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
