<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('student_exception_certificate', function (Blueprint $table) {
            //
            $table->integer('webinar_id')->unsigned()->nullable()->after('bundle_id');
            $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_exception_certificate', function (Blueprint $table) {
            //
            $table->dropForeign(['webinar_id']);
            $table->dropColumn('webinar_id');
        });
    }
};
