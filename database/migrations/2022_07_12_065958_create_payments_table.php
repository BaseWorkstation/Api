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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('paymentable_type')->nullable();
            $table->integer('paymentable_id')->nullable();
            $table->string('mode')->nullable()->comment('enum of payment_mode');
            $table->string('purpose')->nullable()->comment('enum of payment_purpose');
            $table->string('currency_code')->nullable()->comment('enum of currency_code');
            $table->integer('amount')->nullable();
            $table->integer('naira_rate_at_the_time')->nullable();
            $table->integer('total_value_in_naira')->nullable();
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
        Schema::dropIfExists('payments');
    }
};
