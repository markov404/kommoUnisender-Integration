<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration
 * id = 20230621171634
 */
class ChangePrimaryInContacts extends Migration
{
    /**
     * Do the migration
     * @return void
     */
    public function up(): void
    {
        // if (Capsule::schema()->hasTable('emails')) {
        //     Capsule::schema()->table('emails', function(Blueprint $table) {
        //         // $table->unsignedInteger('emails_fk_contacts_id')->change();
        //         $table->dropForeign(['emails_fk_contacts_id']);
        //         // $table->unsignedInteger('emails_fk_contacts_id')->change();
        //         // $table->dropColumn('emails_fk_contacts_id');
        //     });
        // }

        if (Capsule::schema()->hasTable('contacts')) {
            Capsule::schema()->table('contacts', function(Blueprint $table) {
                $table->dropPrimary(['contacts_pk']);
                $table->unsignedInteger('contacts_pk')->change();
                $table->dropColumn(['contacts_pk']);
                $table->unsignedInteger('contacts_kommo_id')->primary();
            });
        }    

        // if (Capsule::schema()->hasTable('emails')) {
        //     Capsule::schema()->table('emails', function(Blueprint $table) {
        //         $table->unsignedInteger('emails_fk_contacts_id');
        //         $table->foreign('emails_fk_contacts_id')
        //               ->references('contacts_kommo_id')
        //               ->on('contacts')
        //               ->onDelete('cascade');
        //     });
        // } 
    }

    /**
     * Undo the migration
     * @return void
     */
    public function down(): void
    {

    }
}
