<?php

namespace App\Core\Console\Commands;

use App\Core\Database\ModuleMigrationManager;
use App\Core\ModuleManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ModuleMakeMigration extends Command
{
    protected $signature = 'module:make-migration
                            {module : The module to make migration for}
                            {name : The name of the migration}
                            {--table= : The table name (optional)}
                            {--create : Create a new table}
                            {--modify : Modify an existing table}';

    protected $description = 'Make a new migration for a specific module';

    protected ModuleManager $moduleManager;
    protected ModuleMigrationManager $migrationManager;

    public function __construct(ModuleManager $moduleManager, ModuleMigrationManager $migrationManager)
    {
        parent::__construct();
        $this->moduleManager = $moduleManager;
        $this->migrationManager = $migrationManager;
    }

    public function handle(): int
    {
        $module = $this->argument('module');
        $name = $this->argument('name');
        $table = $this->option('table');
        $create = $this->option('create');
        $modify = $this->option('modify');

        // Discover modules first
        $this->moduleManager->discoverModules();

        // Validate module exists
        if (!$this->moduleManager->getModule($module)) {
            $this->error("✗ Module '{$module}' not found!");
            $this->line('');
            $this->info('Available modules:');
            foreach ($this->moduleManager->getModules() as $moduleName => $moduleData) {
                $this->line("  - {$moduleName}");
            }
            return 1;
        }

        // Validate migration name
        if (empty($name)) {
            $this->error("✗ Migration name is required!");
            return 1;
        }

        // Determine table name
        if (!$table) {
            if ($create) {
                $table = Str::snake(Str::plural($name));
            } elseif ($modify) {
                $table = Str::snake(Str::plural($name));
            } else {
                // Try to extract table name from migration name
                $table = $this->extractTableNameFromMigrationName($name);
            }
        }

        $this->info("Creating migration for module: {$module}");
        $this->info("Migration name: {$name}");
        $this->info("Table name: {$table}");

        return $this->makeMigration($module, $name, $table, $create, $modify);
    }

    protected function makeMigration(string $module, string $name, string $table, bool $create, bool $modify): int
    {
        try {
            // Create the migration file
            $filepath = $this->migrationManager->createModuleMigration($module, $name);

            // Customize the migration content based on options
            $this->customizeMigrationContent($filepath, $table, $create, $modify);

            $this->info("✓ Migration created successfully!");
            $this->line("File: {$filepath}");

            // Show next steps
            $this->line('');
            $this->info('Next steps:');
            $this->line("1. Edit the migration file: {$filepath}");
            $this->line("2. Run the migration: php artisan module:migrate {$module}");
            $this->line("3. Or run all migrations: php artisan module:migrate");

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to create migration: " . $e->getMessage());
            return 1;
        }
    }

    protected function customizeMigrationContent(string $filepath, string $table, bool $create, bool $modify): void
    {
        $content = file_get_contents($filepath);

        if ($create) {
            // Create table migration
            $content = str_replace(
                [
                    "Schema::create('{{migrationName}}', function (Blueprint \$table) {",
                    "Schema::dropIfExists('{{migrationName}}');"
                ],
                [
                    "Schema::create('{$table}', function (Blueprint \$table) {",
                    "Schema::dropIfExists('{$table}');"
                ],
                $content
            );

            // Add common fields for create table
            $content = str_replace(
                "            \$table->id();\n            \$table->timestamps();",
                "            \$table->id();\n            \$table->timestamps();\n            \n            // Add your table columns here\n            // \$table->string('name');\n            // \$table->text('description')->nullable();\n            // \$table->boolean('is_active')->default(true);",
                $content
            );
        } elseif ($modify) {
            // Modify table migration
            $content = str_replace(
                [
                    "Schema::create('{{migrationName}}', function (Blueprint \$table) {",
                    "Schema::dropIfExists('{{migrationName}}');"
                ],
                [
                    "Schema::table('{$table}', function (Blueprint \$table) {",
                    "Schema::table('{$table}', function (Blueprint \$table) {"
                ],
                $content
            );

            // Update the down method for modify
            $content = str_replace(
                "    public function down(): void\n    {\n        Schema::table('{$table}', function (Blueprint \$table) {\n        });\n    }",
                "    public function down(): void\n    {\n        Schema::table('{$table}', function (Blueprint \$table) {\n            // Reverse the changes made in the up method\n        });\n    }",
                $content
            );

            // Add common modify operations
            $content = str_replace(
                "            \$table->id();\n            \$table->timestamps();",
                "            // Add your table modifications here\n            // \$table->string('new_column');\n            // \$table->dropColumn('old_column');\n            // \$table->renameColumn('old_name', 'new_name');",
                $content
            );
        } else {
            // Default migration (create table)
            $content = str_replace(
                [
                    "Schema::create('{{migrationName}}', function (Blueprint \$table) {",
                    "Schema::dropIfExists('{{migrationName}}');"
                ],
                [
                    "Schema::create('{$table}', function (Blueprint \$table) {",
                    "Schema::dropIfExists('{$table}');"
                ],
                $content
            );
        }

        file_put_contents($filepath, $content);
    }

    protected function extractTableNameFromMigrationName(string $name): string
    {
        // Remove common prefixes and suffixes
        $name = preg_replace('/^(create|modify|add|remove|update|delete)_/', '', $name);
        $name = preg_replace('/_(table|column|index|foreign)$/', '', $name);

        // Convert to snake_case and pluralize
        return Str::snake(Str::plural($name));
    }
}
