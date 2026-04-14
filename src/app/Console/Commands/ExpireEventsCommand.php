<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class ExpireEventsCommand extends Command
{
    protected $signature = 'events:expire';
    protected $description = 'Expire events that passed registration deadline';

    public function handle(): int
    {
        $now = now();
        $expiredCount = 0;

        Event::query()
            ->where('approval_status', 'approved')
            ->where('is_active', true)
            ->whereDate('registration_deadline', '<', $now)
            ->chunkById(100, function ($events) use (&$expiredCount) {

                foreach ($events as $event) {

                    DB::transaction(function () use ($event, &$expiredCount) {

                        // 🔒 lock row (anti race condition)
                        $locked = Event::query()
                            ->whereKey($event->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $locked) {
                            return;
                        }

                        // 🔁 re-check condition (WAJIB)
                        if (
                            $locked->approval_status !== 'approved' ||
                            ! $locked->is_active ||
                            $locked->registration_deadline >= now()
                        ) {
                            return;
                        }

                        // 🔥 expire event
                        $locked->is_active = false;

                        // OPTIONAL (kalau kamu tambahkan field ini)
                        if (property_exists($locked, 'expired_at')) {
                            $locked->expired_at = now();
                        }

                        $locked->save();

                        $expiredCount++;
                    });
                }
            });

        $this->info("Expired events: {$expiredCount}");

        return self::SUCCESS;
    }
}
