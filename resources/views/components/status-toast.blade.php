@props(['statusToast' => []])
@php
    $StatusToast = App\View\DataModels\StatusToast::from($statusToast);
@endphp
@if($StatusToast->message)
    <div class="toast toast-top toast-end z-50">
        <div role="alert" class="alert {{ $StatusToast->alert }}">
            <span>{{ $StatusToast->message }}</span>
        </div>
    </div>
@endif
