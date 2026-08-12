<?php

use App\Routes\Web;
use Laravel\Head\Facades\Head;

Head::title('Contact')
    ->description('Get in touch with our team.');
?>
<x-main>
    <div class="card card-compact m-auto max-w-3xl sm:mt-24">
        <div class="card-body">
            <h1 class="card-title">Contact</h1>

            <div class="mt-4 space-y-6 text-sm leading-relaxed">
                <p>
                    We would like to hear from you. Email us and we will usually reply within
                    {{config('company.response_time')}}.
                </p>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">Email</h2>
                    <p>
                        <a href="mailto:{{config('company.support_email')}}" class="link link-primary">
                            {{config('company.support_email')}}
                        </a>
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">What to include</h2>
                    <p>So we can help on the first reply, tell us:</p>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>The email address on your account, if you have one.</li>
                        <li>What you were trying to do, and what happened instead.</li>
                        <li>The page or link where it happened.</li>
                        <li>Your browser and operating system, for anything that looks like a bug.</li>
                    </ul>
                    <p>Please do not send us your password. We will never ask for it.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">Account help</h2>
                    <p>
                        Some things are faster to do yourself: you can update your name, email address, password, and
                        appearance from your <a href="{{Web::settings->value}}" class="link link-primary">settings</a>.
                        If you cannot sign in, start at the
                        <a href="{{Web::login->value}}" class="link link-primary">login page</a>.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">Privacy and legal</h2>
                    <p>
                        For questions about your data, including access and deletion requests, email the address above
                        and mention the request in your subject line. Our
                        <a href="{{Web::privacyPolicy->value}}" class="link link-primary">Privacy Policy</a> explains
                        what we collect and why, and our
                        <a href="{{Web::termsOfService->value}}" class="link link-primary">Terms of Service</a> cover
                        your use of {{config('app.name')}}.
                    </p>
                </section>

                @if(config('company.address'))
                    <section class="space-y-2">
                        <h2 class="text-base font-semibold">Mailing address</h2>
                        <p class="whitespace-pre-line">{{config('company.address')}}</p>
                    </section>
                @endif
            </div>
        </div>
    </div>
</x-main>
