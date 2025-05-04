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
        Schema::table('sales', function (Blueprint $table) {
            //
            $table->unsignedInteger('transform_bundle_id')->nullable()->default(null)->after('bundle_id');
            $table->foreign('transform_bundle_id')->references('id')->on('bundles')->noActionOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            //
            $table->dropForeign('sales_transform_bundle_id_foreign');
            $table->dropColumn('transform_bundle_id');
        });
    }
};
