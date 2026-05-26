<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class MigrateCvsToPrivate extends Command
{
    protected $signature = 'cvs:migrate-private {--dry-run}';

    protected $description = 'Move CV files from public disk to private disk and keep DB paths unchanged.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Check disks
        try {
            Storage::disk('private')->exists('');
        } catch (\Exception $e) {
            $this->error('Disk `private` is not configured. Configure a private disk in config/filesystems.php before running.');
            return 1;
        }

        $users = User::whereNotNull('cv_path')->get();

        $this->info('Found ' . $users->count() . ' users with cv_path');

        foreach ($users as $user) {
            $path = $user->cv_path;

            if (! Storage::disk('public')->exists($path)) {
                $this->line("Skipping {$user->id}: public file not found: {$path}");
                continue;
            }

            $this->line("Processing user {$user->id}: {$path}");

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($path) {
                $contents = Storage::disk('public')->get($path);
                Storage::disk('private')->put($path, $contents);
                Storage::disk('public')->delete($path);
            });

            $this->info("Migrated {$path}");
        }

        $this->info('Done');

        return 0;
    }
}
