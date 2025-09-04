<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSessionsTableUserIdToUuid extends Migration
{
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Drop the existing user_id column
            $table->dropColumn('user_id');
            // Add user_id as UUID, nullable to match Laravel's default
            $table->uuid('user_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Revert to bigint
            $table->dropColumn('user_id');
            $table->bigInteger('user_id')->nullable()->after('id');
        });
    }
}