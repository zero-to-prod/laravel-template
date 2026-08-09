@props(['pageHeader' => []])
@php
    $PageHeader = App\View\DataModels\PageHeader::from($pageHeader);
@endphp
<div class="min-w-0">
    @if($PageHeader->title)
        <h1 class="{{ $PageHeader->classname }}">{{ $PageHeader->title }}</h1>
    @endif
</div>
@isset($controls)
    <div class="hidden items-center gap-2 header:flex">
        {{ $controls }}
    </div>
@endisset

{{ $slot }}

@isset($controls)
    <div class="sticky bottom-0 z-10 flex items-center gap-2 border-t border-base-300 bg-base-100 px-4 py-3 header:hidden">
        {{ $controls }}
    </div>
@endisset
