<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will drop all tables. Are you sure?')) {
                $this->info('Operation cancelled.');
                return Command::FAILURE;
            }
        }

        $this->info('Dropping all tables...');

        try {
            // Disable foreign key constraints
            DB::statement('SET session_replication_role = replica;');

            // Get all table names
            $tables = DB::select(
                "SELECT tablename FROM pg_tables WHERE schemaname = 'public'"
            );

            $count = 0;
            foreach ($tables as $table) {
                $tableName = $table->tablename;
                DB::statement("DROP TABLE IF EXISTS \"{$tableName}\" CASCADE");
                $this->line("Dropped: {$tableName}");
                $count++;
            }

            // Re-enable foreign key constraints
            DB::statement('SET session_replication_role = DEFAULT;');

            $this->info("Successfully dropped {$count} tables.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to clean database: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
