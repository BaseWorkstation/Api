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
        Schema::create('retainers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('category')->nullable()->nullable();
            $table->integer('workstation_id')->comment('references id on workstations table')->nullable();
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
        Schema::dropIfExists('retainers');
    }
};
