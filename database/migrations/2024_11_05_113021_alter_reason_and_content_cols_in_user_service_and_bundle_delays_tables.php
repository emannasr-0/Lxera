<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('services', function (Blueprint $table) {
        //     $table->text('description')->change();
        // });

        // Schema::table('service_user', function (Blueprint $table) {
        //     $table->text('message')->change();
        //     $table->text('content')->change();
        // });

        // Schema::table('bundle_delay', function (Blueprint $table) {
        //     $table->text('reason')->change();
        // });


        DB::statement('ALTER TABLE services MODIFY COLUMN description text');
        DB::statement('ALTER TABLE service_user MODIFY COLUMN message text');
        DB::statement('ALTER TABLE service_user MODIFY COLUMN content text');
        DB::statement('ALTER TABLE bundle_delay MODIFY COLUMN reason text');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('services', function (Blueprint $table) {
        //     $table->string('description')->change();
        // });

        // Schema::table('service_user', function (Blueprint $table) {
        //     $table->string('message')->change();
        //     $table->string('content')->change();
        // });

        // Schema::table('bundle_delay', function (Blueprint $table) {
        // $table->string('reason')->change();
        // });


        DB::statement('ALTER TABLE services MODIFY COLUMN description varchar(255)');
        DB::statement('ALTER TABLE service_user MODIFY COLUMN message varchar(255)');
        DB::statement('ALTER TABLE service_user MODIFY COLUMN content varchar(255)');
        DB::statement('ALTER TABLE bundle_delay MODIFY COLUMN reason varchar(255)');
    }
};
