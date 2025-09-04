<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeUserIdAndEntityIdToUuidInAuditLogsTable extends Migration
{
    public function up()
    {
        // Enable UUID extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop foreign key constraints if they exist
           // $table->dropForeign(['user_id']); // Adjust if named differently
            $table->dropColumn(['user_id', 'entity_id']);

            // Add user_id and entity_id as uuid
            $table->uuid('user_id')->after('id')->nullable();
            $table->uuid('entity_id')->after('entity_type')->nullable();

            // Re-add foreign key constraint
         //   $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
         //   $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'entity_id']);

            $table->bigInteger('user_id')->nullable()->after('id');
            $table->bigInteger('entity_id')->nullable()->after('entity_type');

         //   $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
}