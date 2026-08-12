<?php

use App\Routes\Web;
use Laravel\Head\Facades\Head;

Head::title('Privacy Policy')
    ->description('How we collect, use, and protect your information.');
?>
<x-main>
    <div class="card card-compact m-auto max-w-3xl sm:mt-24">
        <div class="card-body">
            <h1 class="card-title">Privacy Policy</h1>
            <p class="text-sm opacity-70">Last updated: August 12, 2026</p>

            <div class="mt-4 space-y-6 text-sm leading-relaxed">
                <p>
                    This policy explains what information {{config('app.name')}} ("we", "us") collects when you use
                    our website and services, why we collect it, and the choices you have. By using the service you
                    agree to the handling of information described here.
                </p>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">1. Information You Give Us</h2>
                    <ul class="list-disc space-y-1 pl-5">
                        <li><strong>Account information.</strong> Your name, email address, and password when you register.</li>
                        <li><strong>Profile and settings.</strong> Details you choose to add or change, such as display preferences.</li>
                        <li><strong>Communications.</strong> Messages you send us, including support requests.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">2. Information We Collect Automatically</h2>
                    <ul class="list-disc space-y-1 pl-5">
                        <li><strong>Usage data.</strong> Pages viewed, features used, and actions taken in the service.</li>
                        <li><strong>Device and log data.</strong> IP address, browser type, operating system, referring page, and timestamps.</li>
                        <li><strong>Cookies.</strong> Small files used to keep you signed in, remember preferences, and secure requests.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">3. How We Use Information</h2>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Provide, maintain, and improve the service.</li>
                        <li>Create and secure your account, and verify your email address.</li>
                        <li>Respond to your questions and provide support.</li>
                        <li>Send service messages such as security alerts and account notices.</li>
                        <li>Detect, investigate, and prevent fraud, abuse, and security incidents.</li>
                        <li>Comply with legal obligations and enforce our terms.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">4. Cookies and Similar Technologies</h2>
                    <p>
                        We use cookies that are necessary for the service to work, including session and authentication
                        cookies, and cookies that remember your preferences. Most browsers let you block or delete
                        cookies, but the service may not function correctly without the necessary ones.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">5. How We Share Information</h2>
                    <p>We do not sell your personal information. We share it only as follows:</p>
                    <ul class="list-disc space-y-1 pl-5">
                        <li><strong>Service providers.</strong> Vendors who host our infrastructure, send email, or provide analytics on our behalf, under contract and only for those purposes.</li>
                        <li><strong>Legal reasons.</strong> When required by law, subpoena, or other legal process, or to protect the rights, safety, and property of our users or the public.</li>
                        <li><strong>Business transfers.</strong> In connection with a merger, acquisition, or sale of assets, subject to this policy.</li>
                        <li><strong>With your direction.</strong> When you ask us to share information or connect a third-party service.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">6. Data Retention</h2>
                    <p>
                        We keep personal information for as long as your account is active or as needed to provide the
                        service, and afterwards only as required to meet legal, accounting, or reporting obligations,
                        resolve disputes, and enforce our agreements.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">7. Security</h2>
                    <p>
                        We use reasonable administrative, technical, and physical safeguards to protect information,
                        including encryption in transit and hashed password storage. No method of transmission or
                        storage is completely secure, so we cannot guarantee absolute security. Keep your password
                        confidential and notify us if you believe your account has been compromised.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">8. Your Choices and Rights</h2>
                    <p>
                        You can review and update your account information in your settings, and you may request access
                        to, correction of, or deletion of your personal information. Depending on where you live, you
                        may also have the right to object to or restrict certain processing, and to request a portable
                        copy of your data. You can opt out of non-essential email at any time. To make a request,
                        use our <a href="{{Web::contact->value}}" class="link link-primary">contact page</a>.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">9. Children's Privacy</h2>
                    <p>
                        The service is not directed to children under 13, and we do not knowingly collect their
                        personal information. If you believe a child has provided us information, contact us and we
                        will delete it.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">10. International Transfers</h2>
                    <p>
                        We may process and store information in countries other than the one you live in. Where
                        required, we use appropriate safeguards for those transfers.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">11. Changes to This Policy</h2>
                    <p>
                        We may update this policy from time to time. We will revise the date above and, for material
                        changes, provide additional notice. Continuing to use the service after a change means you
                        accept the updated policy.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">12. Contact Us</h2>
                    <p>
                        Questions about this policy or your information? Reach us through our
                        <a href="{{Web::contact->value}}" class="link link-primary">contact page</a>.
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-main>
