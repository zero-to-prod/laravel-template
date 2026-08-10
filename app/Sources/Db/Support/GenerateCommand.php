<?php

namespace App\Sources\Db\Support;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/** Rebuilds the PHP artifacts from the database schema. */
class GenerateCommand extends Command
{
    /** @var string */
    protected $signature = 'db-model:generate {--schema=App} {--path=} {--dry-run}';

    /** @var string */
    protected $description = 'Scaffold the PHP table enums from the database schema';

    public function handle(): int
    {
        $schema = $this->option('schema');
        $option = $this->option('path');
        $SourceSchema = SourceSchema::make(is_string($schema) ? $schema : '');
        $TableRenderer = new TableRenderer($SourceSchema);
        $path = is_string($option) && $option !== '' ? $option : $SourceSchema->directory;
        $dry_run = $this->option('dry-run') === true;

        File::ensureDirectoryExists($path);

        foreach (DatabaseSchema::read() as $TableDefinition) {
            $file = $path.'/'.$SourceSchema->className($TableDefinition->name).'.php';
            $contents = $TableRenderer->render($TableDefinition);
            $status = match (true) {
                ! File::exists($file) => 'created',
                File::get($file) === $contents => 'unchanged',
                default => 'updated',
            };

            if ($status !== 'unchanged' && ! $dry_run) {
                File::put($file, $contents);
            }

            $this->components->twoColumnDetail($SourceSchema->className($TableDefinition->name), $status);
        }

        $this->components->info($dry_run ? 'Nothing was written.' : 'The PHP table enums were written to ['.$path.'].');

        return self::SUCCESS;
    }
}
