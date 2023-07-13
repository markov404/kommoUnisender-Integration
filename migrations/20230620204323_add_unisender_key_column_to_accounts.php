<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230620204323
 */
class AddUnisenderKeyColumnToAccounts extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        if (Capsule::schema()->hasTable('accounts')) {
            /* @var Blueprint $table */
            Capsule::schema()->table('accounts', function(Blueprint $table) {
                $table->string('unisender_key')->nullable();
            });
        }
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {
        // TODO:
    }
}
