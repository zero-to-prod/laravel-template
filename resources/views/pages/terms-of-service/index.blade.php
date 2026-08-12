<?php

use App\Routes\Web;
use Laravel\Head\Facades\Head;

Head::title('Terms of Service')
    ->description('The terms that govern your use of this service.');
?>
<x-main>
    <div class="card card-compact m-auto max-w-3xl sm:mt-24">
        <div class="card-body">
            <h1 class="card-title">Terms of Service</h1>
            <p class="text-sm opacity-70">Last updated: August 12, 2026</p>

            <div class="mt-4 space-y-6 text-sm leading-relaxed">
                <p>
                    These terms are an agreement between you and {{config('app.name')}} ("we", "us") and govern your
                    use of our website and services (the "Service"). By creating an account or using the Service, you
                    agree to these terms. If you do not agree, do not use the Service.
                </p>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">1. Eligibility</h2>
                    <p>
                        You must be at least 13 years old, and old enough to form a binding contract where you live, to
                        use the Service. If you use the Service on behalf of an organization, you represent that you are
                        authorized to accept these terms for it.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">2. Your Account</h2>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Provide accurate registration information and keep it current.</li>
                        <li>Keep your password confidential; you are responsible for activity under your account.</li>
                        <li>Notify us promptly of any unauthorized use or security breach.</li>
                        <li>Do not share, sell, or transfer your account without our permission.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">3. Acceptable Use</h2>
                    <p>You agree not to:</p>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Break the law, infringe others' rights, or violate these terms.</li>
                        <li>Access accounts, systems, or data you are not authorized to access.</li>
                        <li>Probe, scan, or test the vulnerability of the Service without our written permission.</li>
                        <li>Interfere with or disrupt the Service, including through excessive automated requests.</li>
                        <li>Reverse engineer or copy any part of the Service except as the law permits.</li>
                        <li>Upload malware, or post unlawful, deceptive, harassing, or abusive content.</li>
                        <li>Use the Service to send unsolicited bulk messages.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">4. Your Content</h2>
                    <p>
                        You keep ownership of the content you submit to the Service. You grant us a non-exclusive,
                        worldwide, royalty-free license to host, store, reproduce, and display that content solely to
                        operate and improve the Service. You are responsible for your content and confirm you have the
                        rights needed to submit it.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">5. Our Intellectual Property</h2>
                    <p>
                        The Service, including its software, design, text, and trademarks, belongs to us or our
                        licensors and is protected by intellectual property laws. We grant you a limited, revocable,
                        non-transferable right to use the Service as permitted by these terms. All other rights are
                        reserved.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">6. Third-Party Services</h2>
                    <p>
                        The Service may link to or integrate with services we do not control. We are not responsible for
                        those services, and your use of them is governed by their own terms and privacy practices.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">7. Privacy</h2>
                    <p>
                        Our <a href="{{Web::privacyPolicy->value}}" class="link link-primary">Privacy Policy</a>
                        explains how we handle your information and is part of these terms.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">8. Suspension and Termination</h2>
                    <p>
                        You may stop using the Service and close your account at any time. We may suspend or terminate
                        your access if you violate these terms, if your use creates risk or legal exposure, or if we
                        discontinue the Service. On termination, the rights granted to you end and any provisions that
                        should survive by their nature will survive.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">9. Disclaimer of Warranties</h2>
                    <p>
                        The Service is provided "as is" and "as available", without warranties of any kind, whether
                        express, implied, or statutory, including any implied warranties of merchantability, fitness for
                        a particular purpose, and non-infringement. We do not warrant that the Service will be
                        uninterrupted, secure, or error-free.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">10. Limitation of Liability</h2>
                    <p>
                        To the fullest extent permitted by law, we are not liable for indirect, incidental, special,
                        consequential, or punitive damages, or for lost profits, revenue, data, or goodwill. Our total
                        liability arising out of or relating to the Service is limited to the greater of the amount you
                        paid us in the twelve months before the claim, or one hundred U.S. dollars. Some jurisdictions
                        do not allow these limits, in which case they apply to the extent permitted.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">11. Indemnification</h2>
                    <p>
                        You agree to defend, indemnify, and hold us harmless from claims, damages, and expenses,
                        including reasonable legal fees, arising out of your content, your use of the Service, or your
                        breach of these terms.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">12. Changes to the Service and These Terms</h2>
                    <p>
                        We may modify or discontinue features at any time. We may also update these terms; we will
                        revise the date above and, for material changes, provide additional notice. Continuing to use
                        the Service after a change means you accept the updated terms.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">13. Governing Law and Disputes</h2>
                    <p>
                        These terms are governed by the laws of {{config('company.jurisdiction')}}, without regard to
                        its conflict of law rules, and the courts located in {{config('company.venue')}} have exclusive
                        jurisdiction over any dispute, except that either party may seek injunctive relief in any court
                        of competent jurisdiction.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">14. General</h2>
                    <p>
                        These terms, together with the Privacy Policy, are the entire agreement between you and us
                        regarding the Service. If a provision is found unenforceable, the rest remains in effect. Our
                        failure to enforce a provision is not a waiver of it. You may not assign these terms without our
                        consent; we may assign them in connection with a merger, acquisition, or sale of assets.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold">15. Contact Us</h2>
                    <p>
                        Questions about these terms? Reach us through our
                        <a href="{{Web::contact->value}}" class="link link-primary">contact page</a>.
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-main>
