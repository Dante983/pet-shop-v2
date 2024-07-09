<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('database:truncate-reseed', function () {
    $this->info('Truncating and re-seeding the database...');

    // Disable foreign key checks to truncate tables
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    // Truncate all tables
    $tables = DB::select('SHOW TABLES');
    $dbName = 'Tables_in_' . config('database.connections.mysql.database');
    foreach ($tables as $table) {
        $tableName = $table->$dbName;
        try {
            DB::table($tableName)->truncate();
            $this->info("Truncated table: $tableName");
        } catch (\Exception $e) {
            $this->error("Failed to truncate table: $tableName, Error: " . $e->getMessage());
        }
    }

    // Enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    $this->info('Running migrations...');
    Artisan::call('db:seed');

    $this->info('Database truncated and re-seeded successfully.');
})->purpose('Truncate and re-seed the database')->daily();
