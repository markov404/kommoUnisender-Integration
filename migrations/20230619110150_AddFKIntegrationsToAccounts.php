<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230619110150
 */
class AddFKIntegrationsToAccounts extends Migration
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
                $table->integer('accounts_fk_integrations_id')->unsigned();          
                $table->foreign('accounts_fk_integrations_id')
                      ->references('integrations_pk')
                      ->on('integrations')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {
        if (Capsule::schema()->hasTable('accounts')) {
            if (Capsule::schema()->hasColumn('accounts', 'accounts_fk_integrations_id')) {
                /* @var Blueprint $table */
                Capsule::schema()->table('accounts', function(Blueprint $table) {
                    $table->dropForeign('accounts_fk_integrations_id');
                    $table->dropColumn('accounts_fk_integrations_id');
                });
            }
        }
    }
}
