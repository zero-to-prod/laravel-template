<?php

namespace App\Sources\Db\Support;

use Illuminate\Console\Command;

/** Fails when the PHP artifacts have drifted from the database schema. */
class CheckCommand extends Command
{
    /** @var string */
    protected $signature = 'db-model:check {--schema=App}';

    /** @var string */
    protected $description = 'Validate the PHP table enums against the database schema';

    public function handle(): int
    {
        $schema = $this->option('schema');
        $SourceSchema = SourceSchema::make(is_string($schema) ? $schema : '');
        $differences = new SchemaDiff(DatabaseSchema::read(), $SourceSchema->tables())->differences();

        // writeln() takes the whole list, so an empty one prints nothing.
        $this->output->writeln($differences);
        $this->components->info(count($differences).' difference(s) found. The database is the source of truth: run [db-model:generate] to rebuild the PHP table enums.');

        return $differences === [] ? self::SUCCESS : self::FAILURE;
    }
}
