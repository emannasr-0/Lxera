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
        Schema::table('installments', function (Blueprint $table) {
            //
            $table->string('deadline_type')->default('days');
        });

        Schema::table('selected_installments', function (Blueprint $table) {
            //
            $table->string('deadline_type')->default('days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('installments', function (Blueprint $table) {
            //
            $table->dropColumn('deadline_type');
        });

        Schema::table('selected_installments', function (Blueprint $table) {
            //
            $table->dropColumn('deadline_type');
        });
    }
};
