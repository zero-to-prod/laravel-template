@props(['abilityTable'])
@php
    use App\View\DataModels\AbilityTable;
    $AbilityTable = AbilityTable::from($abilityTable);
@endphp
<form method="POST" action="{{ $AbilityTable->action() }}" class="mt-6 flex flex-col gap-4">
    @csrf
    @foreach($AbilityTable->groups() as $api => $rows)
        <section class="flex flex-col gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/70">{{ ucfirst($api) }} API</h2>
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
