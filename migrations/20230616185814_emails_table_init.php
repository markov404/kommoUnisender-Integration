<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230616185814
 */
class EmailsTableInit extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        /* @var Blueprint $table */
        Capsule::schema()->create('emails', function(Blueprint $table) {
            $table->increments('emails_pk');

            $table->string('emails_email');

            $table->integer('emails_fk_contacts_id')->unsigned();          
            $table->foreign('emails_fk_contacts_id')
                  ->references('contacts_pk')
                  ->on('contacts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {
        Capsule::schema()->dropIfExists('emails');
    }
}
