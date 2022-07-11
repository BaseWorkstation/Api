<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Repositories\PlanRepository;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PlanSeeder extends Seeder
{
    /**
     * declaration of plan repository
     *
     * @var planRepository
     */
    private $planRepository;

    /**
     * Dependency Injection of planRepository.
     *
     * @param  \App\Repositories\PlanRepository $planRepository
     * @return void
     */
    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create new requests
        $gold = new Request([
                "name" => "Gold",
                "price_per_month" => 10000,
                "currency_code" => "NGN",
            ]);

        $silver = new Request([
                "name" => "Silver",
                "price_per_month" => 18000,
                "currency_code" => "NGN",
            ]);

        $platinum = new Request([
                "name" => "Platinum",
                "price_per_month" => 35000,
                "currency_code" => "NGN",
            ]);

        $array = [$gold, $silver, $platinum];

        foreach ($array as $request) {
            // call method to register plan 
            $plan = $this->planRepository->store($request);
        }
    }
}
