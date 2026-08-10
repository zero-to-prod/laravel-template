<?php

// Docs are what an agent reads before touching an endpoint, and a link to a
// file that does not exist is indistinguishable from a file it failed to find.
// Cheaper to fail the gate than to have the reader re-search.
test('every relative markdown link resolves to a file that exists', function (): void {
    $broken = [];

    foreach (markdownFiles(base_path()) as $file) {
        $contents = (string) file_get_contents($file);

        preg_match_all('/]\(([^)#]+?)(?:#[^)]*)?\)/', $contents, $matches);

        foreach ($matches[1] as $target) {
            if (preg_match('#^(https?:|mailto:|/)#', $target) === 1) {
                continue;
            }

            if (! file_exists(dirname($file).'/'.$target)) {
                $broken[] = str_replace(base_path().'/', '', $file).' -> '.$target;
            }
        }
    }

    expect($broken)->toBeEmpty("Markdown links pointing at nothing:\n  - ".implode("\n  - ", $broken));
});

/**
 * The markdown this repo owns: the docs, and the instruction files at the root.
 * `vendor` and `node_modules` are somebody else's to keep honest.
 *
 * @return list<string>
 */
function markdownFiles(string $base): array
{
    $files = glob($base.'/*.md') ?: [];

    $Directory = new RecursiveDirectoryIterator($base.'/docs', FilesystemIterator::SKIP_DOTS);

    foreach (new RecursiveIteratorIterator($Directory) as $File) {
        if ($File instanceof SplFileInfo && $File->getExtension() === 'md') {
            $files[] = $File->getPathname();
        }
    }

    sort($files);

    return $files;
}
