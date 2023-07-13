<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230620174910
 */
class ChangeAccessesStringsToText extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        if (Capsule::schema()->hasTable('accesses')) {
            /* @var Blueprint $table */
            Capsule::schema()->table('accesses', function(Blueprint $table) {
                $table->text('accesses_token')->change();
                $table->text('accesses_refresh_token')->change();
            });
        }
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {

    }
}
