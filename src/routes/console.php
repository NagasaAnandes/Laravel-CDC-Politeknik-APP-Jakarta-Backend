<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test', function () {
    $process = new Process(array_merge([
        PHP_BINARY,
        base_path('vendor/bin/phpunit'),
    ], array_slice($_SERVER['argv'], 2)));

    $process->setEnv([
        'APP_ENV' => 'testing',
        'APP_MAINTENANCE_DRIVER' => 'file',
        'BCRYPT_ROUNDS' => '4',
        'BROADCAST_CONNECTION' => 'null',
        'CACHE_STORE' => 'array',
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => 'db',
        'DB_PORT' => '3306',
        'DB_DATABASE' => 'cdc_db',
        'DB_USERNAME' => 'cdc_user',
        'DB_PASSWORD' => 'CDC_Poltek@ppJKT',
        'MAIL_MAILER' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'SESSION_DRIVER' => 'array',
        'PULSE_ENABLED' => 'false',
        'TELESCOPE_ENABLED' => 'false',
        'NIGHTWATCH_ENABLED' => 'false',
    ]);

    $process->setTimeout(null);
    $process->run(function (string $type, string $buffer): void {
        $this->output->write($buffer);
    });

    return $process->getExitCode() ?? 1;
})->purpose('Run the application test suite');

// 🔥 SCHEDULER EXPIRE EVENT
Schedule::command('events:expire')
    ->everyMinute()
    ->withoutOverlapping();
