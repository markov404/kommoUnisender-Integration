<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230619113722
 */
class ChangeIntegrationsAddIntegrationId extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        if (Capsule::schema()->hasTable('integrations')) {
            /* @var Blueprint $table */
            Capsule::schema()->table('integrations', function(Blueprint $table) {
                $table->string('integrations_integration_identifier');
            });
        }
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {
        if (Capsule::schema()->hasTable('integrations')) {
            if (Capsule::schema()->hasColumn('integrations', 'integrations_integration_identifier')) {
                /* @var Blueprint $table */
                Capsule::schema()->table('integrations', function (Blueprint $table) {
                    $table->dropColumn('integrations_integration_identifier');
                });
            }
        }
    }
}
