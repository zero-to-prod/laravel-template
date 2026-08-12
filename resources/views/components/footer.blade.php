@php
    use App\Routes\Web;
    use App\View\DataModels\Svg;
@endphp
<footer class="footer sm:footer-horizontal border-t border-base-300 bg-base-200 p-10 text-base-content">
    <aside>
        <a href="{{Web::home->value}}" class="flex items-center gap-2" title="Go Home">
            <x-svg :svg="[Svg::name => 'logo', Svg::classname => 'h-8 w-8']"/>
            <span class="text-lg font-semibold">{{config('app.name')}}</span>
        </a>
    </aside>
    <nav>
        <h6 class="footer-title">Support</h6>
        <a href="{{Web::contact->value}}" class="link link-hover">Contact</a>
    </nav>
    <nav>
        <h6 class="footer-title">Legal</h6>
        <a href="{{Web::privacyPolicy->value}}" class="link link-hover">Privacy Policy</a>
        <a href="{{Web::termsOfService->value}}" class="link link-hover">Terms of Service</a>
    </nav>
</footer>
