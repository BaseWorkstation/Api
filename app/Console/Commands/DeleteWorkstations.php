<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Workstation;

class DeleteWorkstations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workstations:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes workstations with IDs 31 and 32';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Workstation::whereIn('id', [31, 32])->delete();
        $this->info('Workstations with IDs 31 and 32 have been deleted.');
        return 0;
    }
}
