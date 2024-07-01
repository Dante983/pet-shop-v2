<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public static function handle()
    {
        Log::info('Truncating and re-seeding the database...');
        $tables = DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . config('database.connections.mysql.database');

        if (!$dbName) {
            Log::error('Database name not found in environment variables.');
            return 1;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $table) {
            Schema::dropIfExists($table->$dbName);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Log::info('Running migrations...');
        Artisan::call('migrate');

        Log::info('Running seeders...');
        Artisan::call('db:seed');

        Log::info('Database truncated and re-seeded successfully.');

        return 0;
    }
}
