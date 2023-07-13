<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * 
 * id = 20230616165942
 */
class IntegrationsTableInit extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        /* @var Blueprint $table */
        Capsule::schema()->create('integrations', function(Blueprint $table) {
            $table->increments('integrations_pk');

            $table->string('integrations_secret_key');
            $table->string('integrations_redirect_url');
            $table->string('integrations_domain');
        });
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {
        Capsule::schema()->drop('integrations');
    }
}
