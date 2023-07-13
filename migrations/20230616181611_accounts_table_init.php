<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230616181611
 */
class AccountsTableInit extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        /* @var Blueprint $table */
        Capsule::schema()->create('accounts', function(Blueprint $table) {
            $table->increments('accounts_pk');
            $table->integer('accounts_kommo_id')->unique();
        });
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {
        Capsule::schema()->dropIfExists('accounts');
    }
}
