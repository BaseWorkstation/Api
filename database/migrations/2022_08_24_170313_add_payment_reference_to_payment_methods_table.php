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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('payment_reference')->after('plan_id')->nullable();
            $table->string('plan_code')->after('payment_reference')->nullable();

            // drop card related columns
            $table->dropColumn('plan_id');
            $table->dropColumn('card_number');
            $table->dropColumn('card_name');
            $table->dropColumn('card_expiry_month');
            $table->dropColumn('card_expiry_year');
            $table->dropColumn('card_cvc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('payment_reference');

            // re-add card related columns
            $table->string('plan_id')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_name')->nullable();
            $table->string('card_expiry_month')->nullable();
            $table->string('card_expiry_year')->nullable();
            $table->string('card_cvc')->nullable();
        });
    }
};
