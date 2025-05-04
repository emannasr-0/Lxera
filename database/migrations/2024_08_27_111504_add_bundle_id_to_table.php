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
        Schema::table('certificates', function (Blueprint $table) {
            //
            $table->unsignedInteger('bundle_id')->nullable()->after('id');
            $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            //
            $table->dropForeign('certificates_bundle_id_foreign');
            $table->dropColumn('bundle_id');
        });
    }
};
