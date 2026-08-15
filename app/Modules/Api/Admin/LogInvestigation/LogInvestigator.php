<?php

namespace App\Modules\Api\Admin\LogInvestigation;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\Http\Controllers\LogsController;

/**
 * @phpstan-type InvestigationInput array{
 *     query?: string,
 *     host?: string,
 *     file?: string,
 *     levels?: list<string>,
 *     environments?: list<string>,
 *     since?: string,
 *     until?: string,
 *     direction?: 'asc'|'desc',
 *     limit?: int,
 *     cursor?: numeric-string,
 *     include_context?: bool
 * }
 * @phpstan-type LogEntry array{
 *     level: string,
 *     message: string,
 *     datetime: string|null,
 *     file_identifier: string,
 *     index: int,
 *     context?: array<string, mixed>,
 *     extra?: array{environment?: string},
 *     full_text?: string
 * }
 * @phpstan-type Finding array{
 *     fingerprint: string,
 *     level: string,
 *     message: string,
 *     first_seen: string|null,
 *     last_seen: string|null,
 *     occurrences: int,
 *     file: string,
 *     representative_entry: int,
 *     context?: array<string, mixed>,
 *     full_text?: string
 * }
 */
class LogInvestigator
{
    private const int batch_size = 100;

    /**
     * @param  InvestigationInput  $input
     * @return array<string, mixed>
     */
    public function investigate(array $input): array
    {
        $page = (int) ($input['cursor'] ?? 1);
        $includeContext = (bool) ($input['include_context'] ?? false);
        $parameters = [
            'query' => $input['query'] ?? (isset($input['file']) ? '' : '.*'),
            'direction' => $input['direction'] ?? 'desc',
            'page' => $page,
            'per_page' => self::batch_size,
            'exclude_full_text' => ! $includeContext,
        ];

        if (isset($input['file'])) {
            $parameters['file'] = $input['file'];
        }

        $Request = Request::create('/admin/logs/api', 'GET', $parameters);
        $data = app(LogsController::class)->index($Request)->getData(true);
        $logs = array_values(array_filter(
            $this->normalizeLogs($data['logs']),
            fn (array $log): bool => $this->matches($log, $input),
        ));
        $limit = (int) ($input['limit'] ?? 10);
        $findings = $this->group($logs, $limit, $includeContext);
        $pagination = $data['pagination'];

        return [
            'summary' => [
                'files_searched' => isset($input['file']) ? 1 : LogViewer::getFiles()->count(),
                'entries_scanned' => count($data['logs']),
                'matches' => count($logs),
                'groups' => count($findings),
                'percent_scanned' => $data['percentScanned'],
            ],
            'findings' => $findings,
            'level_counts' => $this->levelCounts($logs),
            'next_cursor' => $pagination !== null && $page < $pagination['last_page'] ? (string) ($page + 1) : null,
        ];
    }

    /**
     * @param  LogEntry  $log
     * @param  InvestigationInput  $input
     */
    private function matches(array $log, array $input): bool
    {
        $levels = array_map('strtoupper', $input['levels'] ?? []);
        $environments = $input['environments'] ?? [];
        $timestamp = isset($log['datetime']) ? Carbon::parse($log['datetime']) : null;

        return ($levels === [] || in_array(strtoupper((string) $log['level']), $levels, true))
            && ($environments === [] || in_array($log['extra']['environment'] ?? null, $environments, true))
            && (! isset($input['since']) || ($timestamp !== null && $timestamp->greaterThanOrEqualTo(Carbon::parse($input['since']))))
            && (! isset($input['until']) || ($timestamp !== null && $timestamp->lessThanOrEqualTo(Carbon::parse($input['until']))));
    }

    /**
     * @param  list<LogEntry>  $logs
     * @return list<Finding>
     */
    private function group(array $logs, int $limit, bool $includeContext): array
    {
        $groups = [];

        foreach ($logs as $log) {
            $fingerprint = $this->fingerprint($log);

            if (! isset($groups[$fingerprint])) {
                $groups[$fingerprint] = $this->finding($log, $fingerprint, $includeContext);
            } else {
                $groups[$fingerprint]['occurrences']++;
                $groups[$fingerprint]['first_seen'] = $this->earlier($groups[$fingerprint]['first_seen'], $log['datetime'] ?? null);
                $groups[$fingerprint]['last_seen'] = $this->later($groups[$fingerprint]['last_seen'], $log['datetime'] ?? null);
            }
        }

        return array_slice(array_values($groups), 0, $limit);
    }

    /**
     * @param  LogEntry  $log
     * @return Finding
     */
    private function finding(array $log, string $fingerprint, bool $includeContext): array
    {
        $finding = [
            'fingerprint' => $fingerprint,
            'level' => $log['level'],
            'message' => $log['message'],
            'first_seen' => $log['datetime'],
            'last_seen' => $log['datetime'],
            'occurrences' => 1,
            'file' => $log['file_identifier'],
            'representative_entry' => $log['index'],
        ];

        if ($includeContext) {
            $finding['context'] = $log['context'] ?? [];
            $finding['full_text'] = $log['full_text'] ?? '';
        }

        return $finding;
    }

    /** @param LogEntry $log */
    private function fingerprint(array $log): string
    {
        $message = strtolower($log['message']);
        $message = preg_replace('/\b(?:[0-9a-f]{8,}|[0-9]+)\b/i', '{id}', $message) ?? $message;
        $exception = $log['context']['exception'] ?? '';
        $exception = is_string($exception) ? $exception : '';
        preg_match('/^\[object] \(([^(:]+)/', $exception, $matches);

        return substr(sha1(implode('|', [$log['level'], $message, $matches[1] ?? ''])), 0, 12);
    }

    /**
     * @param  list<LogEntry>  $logs
     * @return array<string, int>
     */
    private function levelCounts(array $logs): array
    {
        $counts = [];

        foreach ($logs as $log) {
            $level = $log['level'];
            $counts[$level] = ($counts[$level] ?? 0) + 1;
        }

        ksort($counts);

        return $counts;
    }

    /**
     * @param  list<mixed>  $logs
     * @return list<LogEntry>
     */
    private function normalizeLogs(array $logs): array
    {
        $normalized = [];

        foreach ($logs as $log) {
            if (! is_array($log)
                || ! is_string($log['level'] ?? null)
                || ! is_string($log['message'] ?? null)
                || ! is_string($log['file_identifier'] ?? null)
                || ! is_int($log['index'] ?? null)
            ) {
                continue;
            }

            $datetime = $log['datetime'] ?? null;
            $context = $log['context'] ?? [];
            $environment = is_array($log['extra'] ?? null) ? ($log['extra']['environment'] ?? null) : null;
            $fullText = $log['full_text'] ?? null;

            $entry = [
                'level' => $log['level'],
                'message' => $log['message'],
                'datetime' => is_string($datetime) ? $datetime : null,
                'file_identifier' => $log['file_identifier'],
                'index' => $log['index'],
                'context' => is_array($context) ? $context : [],
                'extra' => is_string($environment) ? ['environment' => $environment] : [],
            ];

            if (is_string($fullText)) {
                $entry['full_text'] = $fullText;
            }

            $normalized[] = $entry;
        }

        return $normalized;
    }

    private function earlier(?string $current, ?string $candidate): ?string
    {
        return $current === null || ($candidate !== null && $candidate < $current) ? $candidate : $current;
    }

    private function later(?string $current, ?string $candidate): ?string
    {
        return $current === null || ($candidate !== null && $candidate > $current) ? $candidate : $current;
    }
}
