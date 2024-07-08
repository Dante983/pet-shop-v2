<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TruncateAndReseedDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:truncate-reseed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate and re-seed the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Truncating and re-seeding the database...');

        // Disable foreign key checks to truncate tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate all tables
        $tables = DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . config('database.connections.mysql.database');
        foreach ($tables as $table) {
            $tableName = $table->$dbName;
            try {
                DB::table($tableName)->truncate();
                Log::info("Truncated table: $tableName");
            } catch (\Exception $e) {
                Log::error("Failed to truncate table: $tableName, Error: " . $e->getMessage());
            }
        }

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Log::info('Running migrations...');
        Artisan::call('db:seed');

        Log::info('Database truncated and re-seeded successfully.');

        return 0;
    }
}
