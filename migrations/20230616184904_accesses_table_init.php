<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230616184904
 */
class AccessesTableInit extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        /* @var Blueprint $table */
        Capsule::schema()->create('accesses', function(Blueprint $table) {
            $table->increments('accesses_pk');

            $table->string('accesses_token');
            $table->string('accesses_base_domain');
            $table->string('accesses_refresh_token');
            $table->integer('accesses_expires');

            $table->integer('accesses_fk_account_kommo_id')->unique();            
            $table->foreign('accesses_fk_account_kommo_id')
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
        Capsule::schema()->dropIfExists('accesses');
    }
}
