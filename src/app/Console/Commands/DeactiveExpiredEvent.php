<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class DeactivateExpiredEvents extends Command
{
    protected $signature = 'events:deactivate-expired';

    protected $description = 'Deactivate approved events that have passed their end_datetime';

    public function handle(): int
    {
        $now = now();

        $count = 0;

        DB::transaction(function () use ($now, &$count) {

            $events = Event::query()
                ->where('is_active', true)
                ->whereNotNull('end_datetime')
                ->where('end_datetime', '<', $now)
                ->lockForUpdate()
                ->get();

            foreach ($events as $event) {

                $event->bypassWorkflowGuard();
                $event->is_active = false;
                $event->save();

                $count++;
            }
        });

        $this->info("Deactivated {$count} expired events.");

        return Command::SUCCESS;
    }
}
