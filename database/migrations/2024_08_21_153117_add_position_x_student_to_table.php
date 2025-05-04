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
        Schema::table('certificates_templates', function (Blueprint $table) {
            //
            $table->string('position_x_student')->nullable()->after('student_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates_templates', function (Blueprint $table) {
            //
            $table->dropColumn('position_x_student');
        });
    }
};
