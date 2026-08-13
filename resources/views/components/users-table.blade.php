@props(['usersTable'])
@php
    use App\View\DataModels\UsersTable;
    $UsersTable = UsersTable::from($usersTable);
@endphp
<div class="mt-6 flex flex-col gap-4">
    <form method="GET" action="{{ $UsersTable->action() }}" class="flex flex-wrap items-end gap-2">
        @foreach($UsersTable->hidden() as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}"/>
        @endforeach
        <div class="w-full max-w-sm">
            <x-text-input :textInput="$UsersTable->searchInput()"/>
        </div>
        <button type="submit" class="btn btn-primary mb-1">Search</button>
        @if($UsersTable->searching())
            <a href="{{ $UsersTable->action() }}" class="btn btn-ghost mb-1">Clear</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-box border border-base-300">
        <table class="table table-zebra">
            <thead>
            <tr>
                @foreach($UsersTable->headers() as $SortableHeader)
                    <th aria-sort="{{ $SortableHeader->ariaSort() }}">
                        <a href="{{ $SortableHeader->url }}" class="group inline-flex items-center gap-1 link link-hover"
                           @if($SortableHeader->title) title="{{ $SortableHeader->title }}" @endif>
                            {{ $SortableHeader->label }}
                            <x-svg :svg="$SortableHeader->svg()"/>
                        </a>
                    </th>
                @endforeach
                <th class="w-0"><span class="sr-only">Edit</span></th>
            </tr>
            </thead>
            <tbody>
            @forelse($UsersTable->rows() as $UserRow)
                <tr>
                    @foreach($UserRow->cells() as $cell)
                        <td class="whitespace-nowrap">{{ $cell }}</td>
                    @endforeach
                    <td class="whitespace-nowrap">
                        <a href="{{ $UserRow->editUrl() }}" class="btn btn-ghost btn-xs">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $UsersTable->span() }}" class="text-center text-base-content/70">No users found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-base-content/70">{{ $UsersTable->summary() }}</p>
        <div class="join">
            <a class="join-item btn btn-sm @if(! $UsersTable->previousUrl()) btn-disabled @endif"
               href="{{ $UsersTable->previousUrl() ?? '#' }}">Previous</a>
            <a class="join-item btn btn-sm @if(! $UsersTable->nextUrl()) btn-disabled @endif"
               href="{{ $UsersTable->nextUrl() ?? '#' }}">Next</a>
        </div>
    </div>
</div>
