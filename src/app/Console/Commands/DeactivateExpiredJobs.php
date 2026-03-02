<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobVacancy;
use Illuminate\Support\Facades\DB;

class DeactivateExpiredJobs extends Command
{
    protected $signature = 'jobs:deactivate-expired';

    protected $description = 'Deactivate published jobs that have passed their expiration date.';

    public function handle(): int
    {
        $this->info('Checking for expired jobs...');

        $count = DB::transaction(function () {

            return JobVacancy::query()
                ->whereNotNull('expired_at')
                ->whereDate('expired_at', '<', now())
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                ]);
        });

        $this->info("Deactivated {$count} expired jobs.");

        return self::SUCCESS;
    }
}
