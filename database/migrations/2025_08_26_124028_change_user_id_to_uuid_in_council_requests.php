<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeUserIdToUuidInCouncilRequests extends Migration
{
    public function up()
    {
        // Enable UUID extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        // Drop existing foreign key constraint if it exists
      

        // Change user_id to uuid
        Schema::table('council_requests', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->change();
        });

     
    }

    public function down()
    {
      

        Schema::table('council_requests', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->change();
        });

     
    }
}