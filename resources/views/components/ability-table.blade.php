@props(['abilityTable'])
@php
    use App\View\DataModels\AbilityTable;
    $AbilityTable = AbilityTable::from($abilityTable);
@endphp
<form method="POST" action="{{ $AbilityTable->action() }}" class="mt-6 flex flex-col gap-4">
    @csrf
    @foreach($AbilityTable->groups() as $api => $rows)
        @php($connection = $AbilityTable->mcpConnection($api))
        <section class="flex flex-col gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/70">{{ ucfirst($api) }} API</h2>
            <details class="group rounded-box border border-base-300 bg-base-200/50">
                <summary class="cursor-pointer list-none p-4 font-semibold marker:content-none">
                    <span class="flex items-center justify-between gap-3">
                        MCP connection
                        <span class="text-base-content/60 transition-transform group-open:rotate-180" aria-hidden="true">⌄</span>
                    </span>
                </summary>
                <div class="border-t border-base-300 p-4">
                    <p class="text-sm text-base-content/70">Use this API's OpenAPI document with your personal access token.</p>
                <dl class="mt-4 grid gap-3 text-sm md:grid-cols-2">
                    <div>
                        <dt class="font-medium text-base-content/70">API base URL</dt>
                        <dd><code class="break-all font-mono">{{ $connection['base_url'] }}</code></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-base-content/70">OpenAPI document</dt>
                        <dd><code class="break-all font-mono">{{ $connection['openapi_url'] }}</code></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-base-content/70">Request headers</dt>
                        <dd><code class="break-all font-mono">{{ $connection['headers'] }}</code></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-base-content/70">Agent documentation</dt>
                        <dd><a href="{{ $connection['llms_url'] }}" class="link link-primary break-all font-mono">{{ $connection['llms_url'] }}</a></dd>
                    </div>
                </dl>
                </div>
            </details>
            <div class="overflow-x-auto rounded-box border border-base-300">
                <table class="table">
            <thead>
            <tr>
                <th>Endpoint</th>
                @foreach($AbilityTable->verbs() as $HttpVerb)
                    <th class="text-center">
                        <button type="button"
                                class="btn btn-ghost btn-xs text-primary"
                                data-ability-column="{{ $HttpVerb->value }}"
                                aria-label="Toggle all {{ $HttpVerb->value }} abilities"
                                aria-pressed="false"
                        >{{ $HttpVerb->value }}</button>
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $AbilityRow)
                <tr class="hover:bg-base-200">
                    <td class="whitespace-nowrap font-mono text-sm">{{ $AbilityRow->path }}</td>
                    @foreach($AbilityTable->verbs() as $HttpVerb)
                        <td class="text-center">
                            @if($AbilityRow->bound($HttpVerb))
                                <input type="checkbox"
                                       class="toggle toggle-sm toggle-primary"
                                       name="{{ AbilityTable::field }}"
                                       value="{{ $AbilityRow->ability($HttpVerb) }}"
                                       data-ability-verb="{{ $HttpVerb->value }}"
                                       aria-label="{{ $AbilityRow->ability($HttpVerb) }}"
                                        @checked($AbilityRow->checked($HttpVerb))
                                />
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($AbilityTable->verbs()) + 1 }}" class="text-center text-base-content/70">
                        This API has no token-authenticated endpoints.
                    </td>
                </tr>
            @endforelse
            </tbody>
                </table>
            </div>
        </section>
    @endforeach

    <div class="flex items-center gap-3">
        <button type="submit" class="btn btn-primary">Save Abilities</button>
        @if($AbilityTable->every())
            <span class="text-sm text-base-content/70">
                This token holds every ability. Saving replaces that with the endpoints ticked above.
            </span>
        @endif
    </div>
</form>
