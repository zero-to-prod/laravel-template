@props(['credentialsTable'])
@php
    use App\View\DataModels\CredentialsTable;
    $CredentialsTable = CredentialsTable::from($credentialsTable);
@endphp
<div class="mt-6 flex flex-col gap-4">
    @if($CredentialsTable->issued)
        <dialog class="modal" data-token-dialog>
            <div class="modal-box">
                <h2 class="text-lg font-semibold">Copy your new token now.</h2>
                <p class="mt-2 text-sm text-base-content/70">It is shown once and cannot be recovered.</p>
                <code class="mt-5 block w-full break-all rounded-box bg-base-200 p-4 font-mono text-sm text-base-content" data-token-value>{{ $CredentialsTable->issued }}</code>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" data-token-dialog-close>Close</button>
                    <button type="button" class="btn btn-primary" data-copy-token>Copy token</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>Close</button></form>
        </dialog>
    @endif

    <form method="POST" action="{{ $CredentialsTable->action() }}" class="flex flex-wrap items-end gap-2">
        @csrf
        <div class="w-full max-w-sm">
            <x-text-input :textInput="$CredentialsTable->nameInput()"/>
        </div>
        <div class="w-full max-w-48">
            <x-text-input :textInput="$CredentialsTable->expiresAtInput()"/>
        </div>
        <button type="submit" class="btn btn-primary mb-1">Create Token</button>
    </form>

    <div class="overflow-x-auto rounded-box border border-base-300">
        <table class="table table-zebra">
            <thead>
            <tr>
                @foreach($CredentialsTable->headers() as $label => $title)
                    <th @if($title) title="{{ $title }}" @endif>{{ $label }}</th>
                @endforeach
                <th class="w-0"><span class="sr-only">Revoke</span></th>
            </tr>
            </thead>
            <tbody>
            @forelse($CredentialsTable->rows() as $CredentialRow)
                <tr>
                    @foreach($CredentialRow->cells() as $cell)
                        <td class="whitespace-nowrap">{{ $cell }}</td>
                    @endforeach
                    <td class="whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            @if($CredentialRow->expired())
                                <span class="badge badge-warning badge-sm">Expired</span>
                            @endif
                            <a href="{{ $CredentialRow->url() }}" class="btn btn-ghost btn-xs">Manage</a>
                            <form method="POST" action="{{ $CredentialRow->url() }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs text-error">Revoke</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $CredentialsTable->span() }}" class="text-center text-base-content/70">No tokens yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
