@props(['abilityTable'])
@php
    use App\View\DataModels\AbilityTable;
    $AbilityTable = AbilityTable::from($abilityTable);
@endphp
<form method="POST" action="{{ $AbilityTable->action() }}" class="mt-6 flex flex-col gap-4">
    @csrf
    <div class="overflow-x-auto rounded-box border border-base-300">
        <table class="table">
            <thead>
            <tr>
                <th>Endpoint</th>
                @foreach($AbilityTable->verbs() as $HttpVerb)
                    <th class="text-center text-primary">{{ $HttpVerb->value }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($AbilityTable->rows() as $AbilityRow)
                <tr class="hover:bg-base-200">
                    <td class="whitespace-nowrap font-mono text-sm">{{ $AbilityRow->path }}</td>
                    @foreach($AbilityTable->verbs() as $HttpVerb)
                        <td class="text-center">
                            @if($AbilityRow->bound($HttpVerb))
                                <input type="checkbox"
                                       class="toggle toggle-sm toggle-primary"
                                       name="{{ AbilityTable::field }}"
                                       value="{{ $AbilityRow->ability($HttpVerb) }}"
                                       aria-label="{{ $AbilityRow->ability($HttpVerb) }}"
                                        @checked($AbilityRow->checked($HttpVerb))
                                />
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="btn btn-primary">Save Abilities</button>
        @if($AbilityTable->every())
            <span class="text-sm text-base-content/70">
                This token holds every ability. Saving replaces that with the endpoints ticked above.
            </span>
        @endif
    </div>
</form>
