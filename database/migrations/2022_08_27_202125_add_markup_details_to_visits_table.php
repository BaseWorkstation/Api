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
        Schema::table('visits', function (Blueprint $table) {
            $table->decimal('base_commission', $precision = 8, $scale = 2)->after('naira_rate_to_currency_at_the_time')->nullable();
            $table->decimal('base_markup', $precision = 8, $scale = 2)->after('base_commission')->nullable();
            $table->decimal('base_share_for_duration', $precision = 8, $scale = 2)->after('base_markup')->nullable();
            $table->decimal('workspace_share_for_duration', $precision = 8, $scale = 2)->after('base_share_for_duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('base_commission');
            $table->dropColumn('base_markup');
            $table->dropColumn('base_share_for_duration');
            $table->dropColumn('workspace_share_for_duration');
        });
    }
};
