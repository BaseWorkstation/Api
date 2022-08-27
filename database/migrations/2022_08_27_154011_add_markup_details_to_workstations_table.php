<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Workstation;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workstations', function (Blueprint $table) {
            $table->decimal('base_commission', $precision = 8, $scale = 2)->after('currency_code')->nullable();
            $table->decimal('base_markup', $precision = 8, $scale = 2)->after('base_commission')->nullable();
            $table->decimal('base_cheaper_compared_to_workstation', $precision = 8, $scale = 2)->after('base_markup')->nullable();
        });

        $workstations = Workstation::all();

        foreach ($workstations as $workstation) {
            if ($workstation->base_commission == null) {
                $workstation->base_commission = 25;
                $workstation->save();
            }
            if ($workstation->base_markup == null) {
                $workstation->base_markup = 2;
                $workstation->save();
            }
            if ($workstation->base_cheaper_compared_to_workstation == null) {
                $workstation->base_cheaper_compared_to_workstation = 20;
                $workstation->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workstations', function (Blueprint $table) {
            $table->dropColumn('base_commission');
            $table->dropColumn('base_markup');
            $table->dropColumn('base_cheaper_compared_to_workstation');
        });
    }
};
