<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\WorkstationRepository;

class RegenerateQRCodesForAllWorkstations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'base:regenerate-qr-codes-for-all-workstations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will regenerate all qr codes again';

    /**
     * private declaration of repositories
     *
     * @var $workstationRepository
     */
    private $workstationRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(WorkstationRepository $workstationRepository)
    {
        parent::__construct();

        $this->workstationRepository = $workstationRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // prompt user to input password
        $password = $this->ask('This is a high-level command: enter password ..');

        if ($password !== '3362') {
            $this->error('wrong password');
        } else {
            $this->info('successful, re-creating all Qr codes..');

            $this->workstationRepository->regenerateQRCodesForAllWorkstations();

            $this->info('All Qr codes have been recreated!');
        }
    }
}
