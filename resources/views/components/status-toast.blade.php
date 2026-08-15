@props(['statusToast' => []])
@php
    use App\Helpers\SvgName;
    use App\View\DataModels\StatusToast;
    use App\View\DataModels\Svg;
    $StatusToast = StatusToast::from($statusToast);
@endphp
@if($StatusToast->message)
    <div class="toast toast-bottom toast-end z-50 pointer-events-none transition duration-300 ease-out motion-reduce:transition-none" data-toast data-autodismiss="5000">
        <div role="alert" class="alert {{ $StatusToast->alert }} pointer-events-auto">
            <span>{{ $StatusToast->message }}</span>
            <button type="button" class="btn btn-ghost btn-xs btn-circle" aria-label="Dismiss" data-dismiss-toast>
                <x-svg :svg="[Svg::name => SvgName::x_mark, Svg::classname => 'h-4 w-4']"/>
            </button>
        </div>
    </div>
@endif
