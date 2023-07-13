<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230619192507
 */
class ChangeIntegrationsPk extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        Capsule::schema()->table('accounts', function(Blueprint $table) {
            $table->dropForeign(['accounts_fk_integrations_id']);
            $table->unsignedInteger('accounts_fk_integrations_id')->change();
            $table->dropColumn(['accounts_fk_integrations_id']);
        });

        if (Capsule::schema()->hasTable('integrations')) {
            /* @var Blueprint $table */
            Capsule::schema()->table('integrations', function(Blueprint $table) {
                $table->dropPrimary();
                $table->unsignedInteger('integrations_pk')->change();
                $table->dropColumn(['integrations_pk']);


                $table->string('integrations_integration_identifier')->change();
                $table->dropColumn(['integrations_integration_identifier']);
                $table->string('integrations_id')->primary();
            });
        } 

        if (Capsule::schema()->hasTable('accounts')) {
            /* @var Blueprint $table */
            Capsule::schema()->table('accounts', function(Blueprint $table) {

                $table->string('accounts_fk_integrations_id');
                $table->foreign('accounts_fk_integrations_id')
                    ->references('integrations_id')
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
        // TODO:
    }
}
