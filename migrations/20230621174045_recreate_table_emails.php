<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * id = 20230621174045
 */
class RecreateTableEmails extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        /* @var Blueprint $table */
        Capsule::schema()->create('emails', function(Blueprint $table) {
            $table->increments('emails_pk');

            $table->string('emails_email');

            $table->unsignedInteger('emails_fk_contacts_id');
            $table->foreign('emails_fk_contacts_id')
                  ->references('contacts_kommo_id')
                  ->on('contacts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
