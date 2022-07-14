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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('workstation_id');
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->string('paidByable_type')->nullable();
            $table->integer('paidByable_id')->nullable();
            $table->integer('payment_method_id')->nullable();
            $table->string('total_minutes_spent')->nullable();
            $table->string('space_price_per_minute_at_the_time')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('naira_rate_to_currency_at_the_time')->nullable();
            $table->string('total_value_of_minutes_spent_in_naira')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visits');
    }
};
