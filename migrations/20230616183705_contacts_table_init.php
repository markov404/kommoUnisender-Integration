<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id - 20230616183705
 */
class ContactsTableInit extends Migration
{
    /**
     * Do the migration
     * @return void 
     */
    public function up(): void
    {
        /* @var Blueprint $table */
        Capsule::schema()->create('contacts', function(Blueprint $table) {
            $table->increments('contacts_pk');
            $table->integer('contacts_fk_account_kommo_id');
            
            $table->foreign('contacts_fk_account_kommo_id')
                  ->references('accounts_kommo_id')
                  ->on('accounts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {
        Capsule::schema()->dropIfExists('contacts');
    }
}
